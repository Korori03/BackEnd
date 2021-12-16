<?php
/*
	* URI Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup URI Class
	* @since 4.0.0
*/

declare(strict_types=1);


class URI
{

/*
	* Get Domain
	* @since 1.0.0
	* @param ()
*/
    public static function domain(): string
    {
        return (self::is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
    }

/*
	* Is SSL
	* @since 1.0.0
	* @param ()
*/
    public static function is_ssl(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? true : false;
    }

/*
	* Get FullPath
	* @since 1.0.0
	* @param (String FileName)
*/
    public static function fullpath(): string
    {
        return self::domain() . $_SERVER['REQUEST_URI'];
    }

/*
	* Get all Components
	* @since 1.0.0
	* @param (String FileName)
*/
    public static function components($segment = "all", $url = null): array
    {
        if (!$url)
            $url = self::fullpath();

        $empty = array();
        $url_compontents = parse_url($url);
        if (empty($url_compontents['host']))
            return  $empty;

        $host = explode('.', $url_compontents['host']);

        preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);
        $sld = substr($domain[0], 0, strpos($domain[0], '.'));
        $tld = substr($domain[0], strpos($domain[0], '.'));

        switch ($segment) {
            case ('all'):
            case ('href'):
                return $url;
                break;

            case ('protocol'):
            case ('scheme'):
            case ('schema'):
                if ($url_compontents['scheme'] != '')
                    return $url_compontents['scheme'];
                else
                    return  $empty;

                break;

            case ('host'):
            case ('hostname'):
                return $url_compontents['host'];
                break;

            case ('subdomain'):
            case ('sub_domain'):
                if ($host[0] != 'www' && $host[0] != $sld)
                    return $host[0];
                else
                    return  $empty;

                break;

            case ('domain'):
                return $domain[0];
                break;

            case ('basedomain'):
            case ('base_domain'):
            case ('origin'):
                return $url_compontents['scheme'] . '://' . $domain[0];
                break;

            case ('sld'):
                return $sld;
                break;

            case ('tld'):
                return $tld;
                break;

            case ('filepath'):
            case ('pathname'):
                return $url_compontents['path'];
                break;

            case ('file'):
            case ('filename'):
                if (str::_contains(basename($url_compontents['path']), '.'))
                    return basename($url_compontents['path']);
                else
                    return  $empty;

                break;

            case ('extension'):
                if (str::_contains(basename($url_compontents['path']), '.'))
                    return explode('.', $url_compontents['path'])[1];
                else
                    return  $empty;

                break;

            case ('path'):
                if (str::_contains(basename($url_compontents['path']), '.'))
                    return str_replace(basename($url_compontents['path']), '', $url_compontents['path']);
                else
                    return $url_compontents['path'];

                break;

            case ('query'):
            case ('queries'):
            case ('search'):
                return $url_compontents['query'];
                break;

            default:
                return $url;
                break;
        }
    }
}
