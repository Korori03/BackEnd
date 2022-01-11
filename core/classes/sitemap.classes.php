<?php
/* --------------------------------
$yourSiteUrl = 'https://houstoncounty.org';
$outputDir = getcwd();

$generator = new Sitemap($yourSiteUrl, $outputDir);

$generator->builder();
*/
class Sitemap
{
    private const MAX_FILE_SIZE = 52428800;
    private const MAX_URLS_PER_SITEMAP = 50000;
    private const MAX_SITEMAPS_PER_INDEX = 50000;
    private const TOTAL_MAX_URLS = self::MAX_URLS_PER_SITEMAP * self::MAX_SITEMAPS_PER_INDEX;
    private const MAX_URL_LEN = 2048;

    private $maxUrlsPerSitemap = self::MAX_URLS_PER_SITEMAP;
    private static $_errors = array();

    private
        $classVersion           =   "4.3.3",
        $robotsFileName		    =   "robots.txt",
        $sitemapFileName        =   "sitemap.xml",
        $sitemapXMLFileName   =   "sitemap-index.xml",
        $sitemapXMLFileNameFormat = '',
        $xmlformat              =   "sm-%d.xml",
        $flushedSitemapSize     =   0,
        $flushedSitemapCounter  =   0,
        $flushedSitemaps        =   [],
        $isSitemapStarted       =   false,
        $totalUrlCount          =   0,
        $urlsetClosingTagLen    =   10,
        $sitemapUrlCount        =   0,
        $generatedFiles         =   [],
        $isCompressionEnabled   =   false;

    private $searchEngines = [
        [
            "http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=USERID&url=",
            "http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=",
        ],
        "http://www.google.com/ping?sitemap=",
        "http://submissions.ask.com/ping?sitemap=",
        "http://www.bing.com/ping?sitemap=",
        "http://www.webmaster.yandex.ru/ping?sitemap=",
    ];
    private $sampleRobotsLines = [
        "User-agent: *",
        "Allow: /",
    ];
    private $frequency = [
        'always',
        'hourly',
        'daily',
        'weekly',
        'monthly',
        'yearly',
        'never',
    ];
    private $validPriorities = [
        0.0,
        0.1,
        0.2,
        0.3,
        0.4,
        0.5,
        0.6,
        0.7,
        0.8,
        0.9,
        1.0,
    ];

    private $fs;
    private $uploader;
    private $xmlWriter;
    private $baseURL;
    private $basePath;

    public function __construct(string $baseURL, string $basePath = "", FileSystem $fs = null,Uploader $uploader= null)
    {
        $this->urls = [];
        $this->baseURL = rtrim($baseURL, '/'). DIRECTORY_SEPARATOR;
        $this->uploader =$uploader === null? new Uploader():$uploader;
        $this->fs = $fs === null? new FileSystem():$fs;

        if (!$this->fs->_writable($basePath))
            self::addError(sprintf('The provided basePath (%s) should be a writable directory,', $basePath) .' please check its existence and permissions');

        $this->basePath = $basePath;

        $this->xmlWriter = $this->createXmlWriter();
        $this->sitemapXMLFileNameFormat = sprintf($this->xmlformat, time());
    }

    public function builder(){
       $this->setMaxUrlsPerSitemap(50000);
        $this->setSitemapFileName($this->sitemapFileName);
        $this->setsitemapXMLFileName($this->sitemapXMLFileName);
        $this->scanPages();
        $this->flush();
        $this->finalize();
        $this->updateRobots();
        $this->submitSitemap();
    }

    private function scanPages(){
        $pages = Database::getInstance()->get(Config::get('table/content'));
        foreach($pages->results() as $page)
            $this->addURL(sprintf('%s/%s-%s',Slug::_url(str_replace('&amp;','', WebObjects::getDeptName($page->did))),$page->page,Slug::_url($page->title)), new DateTime(), 'always', 0.5);
    }

    private function createXmlWriter(): XMLWriter
    {
        $w = new XMLWriter();
        $w->openMemory();
        $w->setIndent(true);
        return $w;
    }

    public function setSitemapFilename(string $filename = ''): Sitemap
    {
        if (strlen($filename) === 0)
            self::addError('Sitemap filename should not be empty');

        if (pathinfo($filename, PATHINFO_EXTENSION) !== 'xml')
            self::addError('Sitemap filename should have *.xml extension');

        $this->sitemapFileName = $filename;
        return $this;
    }

    public function setsitemapXMLFileName(string $filename = ''): Sitemap
    {
        if (strlen($filename) === 0)
            self::addError('filename should not be empty');

        $this->sitemapXMLFileName = $filename;
        return $this;
    }

    public function setRobotsFileName(string $filename): Sitemap
    {
        if (strlen($filename) === 0)
            self::addError('Filename should not be empty');

        $this->robotsFileName = $filename;
        return $this;
    }

    public function setMaxUrlsPerSitemap(int $value): Sitemap
    {
        if ($value < 1 || self::MAX_URLS_PER_SITEMAP < $value)
            self::addError( sprintf('Value %d is out of range 1-%d', $value, self::MAX_URLS_PER_SITEMAP));

        $this->maxUrlsPerSitemap = $value;
        return $this;
    }

    public function enableCompression(): Sitemap
    {
        $this->isCompressionEnabled = true;
        return $this;
    }

    public function disableCompression(): Sitemap
    {
        $this->isCompressionEnabled = false;
        return $this;
    }

    public function isCompressionEnabled(): bool
    {
        return $this->isCompressionEnabled;
    }

    public function validate(
        string $path,
        string $changeFrequency = null,
        float $priority = null)
    {
        if (!(1 <= mb_strlen($path) && mb_strlen($path) <= self::MAX_URL_LEN)) {
            self::addError(
                sprintf("The urlPath argument length must be between 1 and %d.", self::MAX_URL_LEN)
            );
        }
        if ($changeFrequency !== null && !in_array($changeFrequency, $this->frequency)) {
            self::addError(
                'The change frequency argument should be one of: %s' . implode(',', $this->frequency)
            );
        }
        if ($priority !== null && !in_array($priority, $this->validPriorities)) {
            self::addError("Priority argument should be a float number in the range [0.0..1.0]");
        }

    }

    public function addURL(
        string $path,
        DateTime $lastModified = null,
        string $changeFrequency = null,
        float $priority = null
    ): Sitemap
    {
        $this->validate($path, $changeFrequency, $priority);

        if ($this->totalUrlCount >= self::TOTAL_MAX_URLS)
            self::addError(sprintf("Max url limit reached (%d)", self::TOTAL_MAX_URLS));

        if (!$this->isSitemapStarted)
            $this->writeSitemapStart();

        $this->writeSitemapUrl($this->baseURL . $path, $lastModified, $changeFrequency, $priority);

        if ($this->totalUrlCount % 1000 === 0 || $this->sitemapUrlCount >= $this->maxUrlsPerSitemap)
            $this->flushWriter();

        if ($this->sitemapUrlCount === $this->maxUrlsPerSitemap)
            $this->writeSitemapEnd();

        return $this;
    }

    private function writeSitemapStart()
    {
        $this->xmlWriter->startDocument("1.0", "UTF-8");
        $this->xmlWriter->writeComment(sprintf('generator-class="%s"', get_class($this)));
        $this->xmlWriter->writeComment(sprintf('generator-version="%s"', $this->classVersion));
        $this->xmlWriter->writeComment(sprintf('generated-on="%s"', date::_custom(null,'c')));
        $this->xmlWriter->startElement('urlset');
        $this->xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->xmlWriter->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        $this->xmlWriter->writeAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');
        $this->xmlWriter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->xmlWriter->writeAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
        $this->isSitemapStarted = true;
    }

    private function writeSitemapUrl($loc, $lastModified, $changeFrequency, $priority)
    {
        $this->xmlWriter->startElement('url');
        $this->xmlWriter->writeElement('loc', htmlspecialchars($loc, ENT_QUOTES));

        if ($lastModified !== null)
            $this->xmlWriter->writeElement('lastmod', $lastModified->format(DateTime::ATOM));

        if ($changeFrequency !== null)
            $this->xmlWriter->writeElement('changefreq', $changeFrequency);

        if ($priority !== null)
            $this->xmlWriter->writeElement('priority', number_format($priority, 1, ".", ""));

        $this->xmlWriter->endElement();
        $this->sitemapUrlCount++;
        $this->totalUrlCount++;
    }

    private function flushWriter()
    {
        $targetSitemapFilepath = $this->basePath . DIRECTORY_SEPARATOR. sprintf($this->sitemapXMLFileNameFormat, $this->flushedSitemapCounter);
        $flushedString = $this->xmlWriter->outputMemory(true);
        $flushedStringLen = mb_strlen($flushedString);

        if ($flushedStringLen === 0)
            return;

        $this->flushedSitemapSize += $flushedStringLen;

        if ($this->flushedSitemapSize > self::MAX_FILE_SIZE - $this->urlsetClosingTagLen) {
            $this->writeSitemapEnd();
            $this->writeSitemapStart();
        }

        $this->uploader->_writecontents($targetSitemapFilepath, $flushedString);
    }

    private function writeSitemapEnd()
    {
        $targetSitemapFilepath = $this->basePath . DIRECTORY_SEPARATOR.sprintf($this->sitemapXMLFileNameFormat, $this->flushedSitemapCounter);
        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
        $this->uploader->_writecontents($targetSitemapFilepath, $this->xmlWriter->flush(true));
        $this->isSitemapStarted = false;
        $this->flushedSitemaps[] = $targetSitemapFilepath;
        $this->flushedSitemapCounter++;
        $this->sitemapUrlCount = 0;
    }


    public function flush()
    {
        $this->flushWriter();
        if ($this->isSitemapStarted)
            $this->writeSitemapEnd();

    }


    public function finalize()
    {
        $this->generatedFiles = [];

        if (count($this->flushedSitemaps) === 1) {
            $targetSitemapFilename = $this->sitemapFileName;
            if ($this->isCompressionEnabled)
                $targetSitemapFilename .= '.gz';


            $targetSitemapFilepath = $this->basePath . DIRECTORY_SEPARATOR.$targetSitemapFilename;

            if ($this->isCompressionEnabled) {
                $this->fs->_copy($this->flushedSitemaps[0], 'compress.zlib://' . $targetSitemapFilepath);
                $this->fs->_remove($this->flushedSitemaps[0]);
            } else {
                $this->fs->_rename($this->flushedSitemaps[0], $targetSitemapFilepath);
            }
            $this->generatedFiles['sitemaps_location'] = [$targetSitemapFilepath];
            $this->generatedFiles['sitemaps_index_url'] = $this->baseURL  . $targetSitemapFilename;
        } else if (count($this->flushedSitemaps) > 1) {
            $ext = '.' . pathinfo($this->sitemapFileName, PATHINFO_EXTENSION);
            $targetExt = $ext;
            if ($this->isCompressionEnabled)
                $targetExt .= '.gz';

            $sitemapsUrls = [];
            $targetSitemapFilepaths = [];
            foreach ($this->flushedSitemaps as $i => $flushedSitemap) {
                $targetSitemapFilename = str_replace($ext, ($i + 1) . $targetExt, $this->sitemapFileName);
                $targetSitemapFilepath = $this->basePath . DIRECTORY_SEPARATOR.$targetSitemapFilename;

                if ($this->isCompressionEnabled) {
                    $this->fs->_copy($flushedSitemap, 'compress.zlib://' . $targetSitemapFilepath);
                    $this->fs->_remove($flushedSitemap);
                } else {
                    $this->fs->_rename($flushedSitemap, $targetSitemapFilepath);
                }
                $sitemapsUrls[] = htmlspecialchars($this->baseURL . '/' . $targetSitemapFilename, ENT_QUOTES);
                $targetSitemapFilepaths[] = $targetSitemapFilepath;
            }

            $targetSitemapIndexFilepath = $this->basePath .DIRECTORY_SEPARATOR. $this->sitemapXMLFileName;
            $this->createSitemapIndex($sitemapsUrls, $targetSitemapIndexFilepath);
            $this->generatedFiles['sitemaps_location'] = $targetSitemapFilepaths;
            $this->generatedFiles['sitemaps_index_location'] = $targetSitemapIndexFilepath;
            $this->generatedFiles['sitemaps_index_url'] = $this->baseURL . '/' . $this->sitemapXMLFileName;
        } else {
            self::addError('failed to finalize, please add urls and flush first');
        }
    }

    private function createSitemapIndex($sitemapsUrls, $sitemapXMLFileName)
    {
        $this->xmlWriter->flush(true);
        $this->writeSitemapIndexStart();
        foreach ($sitemapsUrls as $sitemapsUrl)
            $this->writeSitemapIndexUrl($sitemapsUrl);

        $this->writeSitemapIndexEnd();
        $this->uploader->_writecontents(
            $sitemapXMLFileName,
            $this->xmlWriter->flush(true)
        );
    }

    private function writeSitemapIndexStart()
    {
        $this->xmlWriter->startDocument("1.0", "UTF-8");
        $this->xmlWriter->writeComment(sprintf('generator-class="%s"', get_class($this)));
        $this->xmlWriter->writeComment(sprintf('generator-version="%s"', $this->classVersion));
        $this->xmlWriter->writeComment(sprintf('generated-on="%s"',date::_custom(null,'c')));
        $this->xmlWriter->startElement('sitemapindex');
        $this->xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $this->xmlWriter->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->xmlWriter->writeAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
    }

    private function writeSitemapIndexUrl($url)
    {
        $this->xmlWriter->startElement('sitemap');
        $this->xmlWriter->writeElement('loc', htmlspecialchars($url, ENT_QUOTES));
        $this->xmlWriter->writeElement('lastmod',date::_custom(null,'c'));
        $this->xmlWriter->endElement(); // sitemap
    }

    private function writeSitemapIndexEnd()
    {
        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
    }

    public function getGeneratedFiles(): array
    {
        return $this->generatedFiles;
    }

    public function submitSitemap($yahooAppId = null): array
    {
        if (count($this->generatedFiles) === 0)
            self::addError("No Generated Files");

        $searchEngines = $this->searchEngines;
        $searchEngines[0] = isset($yahooAppId) ?
            str_replace("USERID", $yahooAppId, $searchEngines[0][0]) :
            $searchEngines[0][1];
        $result = [];
        for ($i = 0; $i < count($searchEngines); $i++) {
            $submitUrl = $searchEngines[$i] . htmlspecialchars($this->generatedFiles['sitemaps_index_url'], ENT_QUOTES);

            $submitSite = $this->fs->curl_init($submitUrl);
            $this->fs->curl_setopt($submitSite, CURLOPT_RETURNTRANSFER, true);
            $responseContent = $this->fs->curl_exec($submitSite);
            $response = $this->fs->curl_getinfo($submitSite);
            $submitSiteShort = array_reverse(explode(".", parse_url($searchEngines[$i], PHP_URL_HOST)));
            $result[] = [
                "site" => $submitSiteShort[1] . "." . $submitSiteShort[0],
                "fullsite" => $submitUrl,
                "http_code" => $response['http_code'],
                "message" => str_replace("\n", " ", strip_tags($responseContent)),
            ];
        }
        return $result;
    }

    public function updateRobots(): Sitemap
    {
        if (count($this->generatedFiles) === 0)
            self::addError("To update robots.txt, call finalize() first.");

        $robotsFilePath = $this->basePath . DIRECTORY_SEPARATOR.$this->robotsFileName;

        $robotsFileContent = $this->createNewRobotsContentFromFile($robotsFilePath);
        $this->uploader->_writecontents($robotsFilePath, $robotsFileContent,'w');

        return $this;
    }

    private function createNewRobotsContentFromFile($filepath): string
    {
        if ($this->fs->_exist($filepath)) {
            $robotsFileContent = "";
            $robotsFile = explode(PHP_EOL, $filepath);
            foreach ($robotsFile as $key => $value) {
                if (substr($value, 0, 8) == 'Sitemap:') {
                    unset($robotsFile[$key]);
                } else {
                    $robotsFileContent .= $value . PHP_EOL;
                }
            }
        } else {
            $robotsFileContent = $this->getSampleRobotsContent();
        }

        $robotsFileContent .= "Sitemap: {$this->generatedFiles['sitemaps_index_url']}";

        return $robotsFileContent;
    }


    private function getSampleRobotsContent(): string
    {
        return implode(PHP_EOL, $this->sampleRobotsLines) . PHP_EOL;
    }

    /*
		* Add Error
		* @since 4.0.0
		* @param (String Error)
	*/
	private static function addError(string $error){
		self::$_errors[] = $error;
	}
	/*
	* Error Call
	* @since 4.0.0
	* @param ()
	*/
	public static function errors(){
		return self::$_errors;
	}
}
