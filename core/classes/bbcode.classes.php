<?php
/*
	* Setup BBcode Class
	* @Version 2.1.0
	* Developed by: Ami (亜美) Denault
*/

declare(strict_types=1);
class BBCODE{

/*
	* Format BBCode
	* @since 2.1
	* @update 3.1
	*		(Created a much more reliable bbcode method using better regex)
	* @update 3.2
	*		(Updated HTML 5 Video)
	* @Param (String Text)

*/	

	public static function format(string $text): string{
	
		$text = html_entity_decode ($text,ENT_QUOTES,'UTF-8');				
	    
		$text = str::_toAscii($text);
		$bbcodes = array(
			'/\[br\]/is' => '<br/>',
			'/\[b\](.+?)\[\/b\]/is' => '<strong>$1</strong>',
			'/\[alternate\](.+?)\[\/alternate\]/is' => '<div class="altbbcode_tr">$1</div>',
			'/\[i\](.+?)\[\/i\]/is' => '<em>$1</em>',
			'/\[u\](.+?)\[\/u\]/is' => '<span style=\'text-decoration: underline;\'>$1</span>',
			'/\[size\=(8|10|12|14|18|24|36)\](.+?)\[\/size\]/is' => '<span style=\'font-size:$1px\'>$2</span>',
			'/\[mail\](.+?)\[\/mail\]/is' =>  '<a href=\'mailto:$1\'>$1</a>',
			'/\[mail\=(.+?)\](.+?)\[\/mail\]/is' => '<a href=\'mailto:$1\'>$2</a>',	
			'/\[email\](.+?)\[\/email\]/iUs' =>  '<a href=\'mailto:$1\'>$1</a>',
			'/\[email=(.+?)\](.+?)\[\/email\]/is' => '<a href=\'mailto:$1\'>$2</a>',
			'/\[left\](.+?)\[\/left\]/is' => '<div style=\'text-align:left\'>$1</div>',
			'/\[center\](.+?)\[\/center\]/is' => '<div style=\'text-align:center\'>$1</div>',
			'/\[right\](.+?)\[\/right\]/is' => '<div style=\'text-align:right\'>$1</div>',
			'/\[justify\](.+?)\[\/justify\]/is' => 'divp style=\'text-align:justify\'>$1</div>',
			'/\[align=(left|right|center)\](.+?)\[\/align\]/is' => '<div style=\'text-align:$1\'>$2</div>',
			'/\[faqs=(.+?)\](.+?)\[\/faqs\]/is' => '<div class="faqs"><a class="faq_question" href="#0">$1</a><div class="faq_answer"><p>$2</p></div></div>',
			'/\[color\=(.+?)\](.+?)\[\/color\]/is' => '<span style=\'color:$1\'>$2</span>',	
			'/\[colour\=(.+?)\](.+?)\[\/colour\]/is' => '<span style=\'color:$1\'>$2</span>',
			'/\[font\=(courier|arial|arial black|impact|verdana|times new roman|georgia|andale mono|trebuchet ms|comic sans ms)\](.+?)\[\/font\]/is' => '<span style=\'font-family :$1\'>$2</span>',
			'/\[indent\](.+?)\[\/indent\]/is' => '<span style=\'padding-left:10px;\'>$1</span>',
			'/\[tab\]/is' => '<div style=\'width:20px;display:inline;\'>&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;&thinsp;</div>',
			'/\[highlight\](.+?)\[\/highlight\]/is' => '<span style=\'background: #FFEB90 none repeat-x;color: #2C4F68;font-weight: bold;\'>$1</span>',	
			'/\[s\](.+?)\[\/s\]/is' => '<span style=\'text-decoration:line-through;\'>$1</span>',
			'/\[sub\](.+?)\[\/sub\]/is' => '<span class=\'subScript\'>$1</span>',
			'/\[sup\](.+?)\[\/sup\]/is' => '<span class=\'supScript\'>$1</span>',
			'/\[hr\]/iUs' => '<hr />',	
			'/\[video\](.+?)\[\/video\]/is' => '<video src="$1" controls="" preload=""></video>',
		);
		$bbcodes_fun = array(
			'/\[table\=(.+?)\](.+?)\[\/table\]/is' => 'bbcode_table($m[1],$m[2])',
			'/\[youtube\](.+?)\[\/youtube\]/is' => 'bbcode_youtube($m[1])',
			'/\[list\](.+?)\[\/list\]/is' => 'bbcode_list($m[1])',
			'/\[list\=(lower-roman|upper-roman|lower-alpha|upper-alpha|bullet)\](.+?)\[\/list\]/is' => 'bbcode_list($m[2],$m[1])',
			'/\[url\=(.+?)\](.+?)\[\/url\]/is' => 'bbcode_url($m[1],$m[2])',	
			'/\[url\](.+?)\[\/url\]/is' => 'bbcode_url($m[1],$m[1])',
			'/\[link\=(.+?)\](.+?)\[\/link\]/is' => 'bbcode_url($m[1],$m[2])',
			'/\[link\](.+?)\[\/link\]/is' => 'bbcode_url($m[1],$m[1])',
			'/\[img\](.+?)\[\/img\]/is' => 'img_src($m[1])',
			'/\[img width=(.+?) height=(.+?)\](.+?)\[\/img\]/is' => 'img_src($m[1],$m[2],$m[3])',
			'/\([0-9]{3}\)([-.\s])[0-9]{3}([-.\s])[0-9]{4}/is'=>'tel_href($m[1])',
			'/[0-9]{3}([-.\s])[0-9]{3}([-.\s])[0-9]{4}/is'=>'tel_href($m[1])'
		);
		foreach ($bbcodes_fun as $search => $replace_fun){
			$fun = explode('(',$replace_fun);
			$getfunction = $fun[0];
			if (function_exists($getfunction)){
				if($getfunction == 'bbcode_url'){
					$text = preg_replace_callback($search, function($m){
						if(count($m) == 3)
							return bbcode_url($m[1],$m[2]);
						else
							return bbcode_url($m[1],$m[1]);
					}, $text);
				}
				else if($getfunction == 'img_src'){
					$text = preg_replace_callback($search, function($m){
						if(count($m) == 4)
							return img_src($m[3],$m[1],$m[2]);
						else
							return img_src($m[1]);
					}, $text);
				}
				else if($getfunction == 'bbcode_table'){
					$text = preg_replace_callback($search, function($m){
							return bbcode_table($m[1],$m[2]);
					}, $text);
				}
				else if($getfunction == 'bbcode_youtube'){
					$text = preg_replace_callback($search, function($m){
						return bbcode_youtube($m[1]);
					}, $text);
				}
				else if($getfunction == 'tel_href'){
					$text = preg_replace_callback($search, function($m){
						return tel_href($m[0]);
					}, $text);
				}
				else if($getfunction == 'bbcode_list'){
					$text = preg_replace_callback($search, function($m){
						if(count($m) == 3)
							return bbcode_list($m[2],$m[1]);
						else
							return bbcode_list($m[1]);
					}, $text);
				}
				
			}
		}
		
		foreach ($bbcodes as $search => $replace)
			$text = preg_replace($search, $replace, $text);	

		$text = nl2br($text);

		return $text;
	}
}


/*
	* BBcode URL
	* @since 2.1	
	* @Param (String Link, String Title)
*/	
	function bbcode_url(string $link,string $title): string{
		$url = '<a href="'.  str_replace(' ','',str::_trim($link)) . '">'.$title.'</a>';
		
		if(substr($link,0,1) =='/')
			$url =  '<a href='.$link . '>'.$title.'</a>';
			
		return $url;
	}

/*
	* Check if Valid Url
	* @Since 3.1.1
	* @Param (String Url)
*/		
	function isValidUrl(string $url): bool{
        if(!$url || !is_string($url))
            return false;

		if( ! preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url) )
            return false;

         if(getHttpResponseCode_using_curl($url) != 200)
            return false;
        
        return true;
    }

/*
	* Get Http Response 
	* @Since 3.1.1
	* @Param (String Url, Bool Follow Redirects)
*/
    function getHttpResponseCode_using_curl(string $url,bool $followredirects = true): string{
        if(! $url || ! is_string($url))
            return false;
        
        $ch = @curl_init($url);
        if($ch === false){
            return false;
        }
        @curl_setopt($ch, CURLOPT_HEADER         ,true); 
        @curl_setopt($ch, CURLOPT_NOBODY         ,true);
        @curl_setopt($ch, CURLOPT_RETURNTRANSFER ,true);
		

		@curl_setopt($ch,CURLOPT_TIMEOUT,3);
		@curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3);
        if($followredirects){
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,true);
            @curl_setopt($ch, CURLOPT_MAXREDIRS      ,10);  
        }else
            @curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,false);
        
        @curl_exec($ch);
        if(@curl_errno($ch)){ 
            @curl_close($ch);
            return false;
        }
        $code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
        @curl_close($ch);
        return $code;
    }


/*
	* BBcode Table
	* @since 2.1
	* @param Rows, Number of Items [c], Width of table, alignement of table
*/	
	function bbcode_table(string $rows,string $items,string $align = NULL):string{
		$rows = cast::_int($rows);
		$objects = explode('[c]',$items);
		$table = '<table class="rounded-corner" cellpadding="0" cellspacing="0" '. (($align !=NULL)? 'margin-left: auto;margin-right: auto;': '').'><tbody>';
		$rc = 0;

		for($x = 0; $x < count($objects);$x++){	
			if($rc == 0)
				$table .= '<tr>';
				
			$table .='<td>'. $objects[$x] . '</td>';
				
			if(count($objects) - 1 == $x && $rc <= $rows - 2)
				$table .= '<td></td></tr>';
			else{
				if($rc == $rows - 1){
					$table .= '</tr>';
					$rc = 0;
				}
				else
					$rc++;
			}
		}
		$table .= '</tbody></table>';
		return $table;
	}

/*
	* BBcode List
	* @since 2.1
	* @param Number of Items [*], Style (bullet)
*/	
	function bbcode_list(string $items,string $style = NULL):string{
	
		$objects = explode('[*]',$items);
		$list = '<ol'. (($style !=NULL)? ' style="list-style-type:'.(($style == 'bullet')? 'disc' : $style).';"': '').'>';
		
		for($x = 1; $x < count($objects);$x++)
			$list .='<li>'. $objects[$x] . '</li>';	

		$list .= '</ol>';
		return $list;
	}

/*
	* Convert Special Characters to Normalize Ascii
	* @since 2.1
	* @param (Input String)

*/	
	function makeASCII(string $a):string{
		$a = preg_replace("/[^A-Za-z0-9\s\s+\.\:\-\/%+\(\)\*\&\$\#\!\@\?\=\"\';\n\t\r]/"," ",$a);
		return $a;
	}

/*
	* Telephone Href
	* @since 3.1
	* @param (Input String Telephone)

*/		
	function tel_href(string $tel):string{
		$tel_parse = trim(str_replace(array("(",")","-","."," ","<br/>","[br]","]","[",":"),"",trim($tel)));
		return "<a  role='link' class='link' href='tel:$tel_parse'>$tel</a>";
	}

/*
	* Image Src Check
	* @since 3.1
	* @param (String Source, Integer Width, Integer Height)
*/	
	function img_src(string $src,int $width=0,int $height=0):string{
		$src_parse = str_replace(array(" ","%20"),"",trim($src));

		if(substr($src_parse,0,1) == '/'){
			if(FileSystem::_exist(substr($src_parse,1))){
				if($width != 0 && $height != 0)
					return '<img src="'.$src_parse.'" alt="image" style="width:'.$width.'px; height:'.$height.'px;" />';
				else
					return '<img src="'.$src_parse.'" alt="image" style="max-width:250px; max-height:250px;" />';
			}
			else
				return 'Image Error';
		}
		else{
				if($width != 0 && $height != 0)
					return '<img src="'.$src_parse.'" alt="image" style="width:'.$width.'px; height:'.$height.'px;" />';
				else
					return '<img src="'.$src_parse.'" alt="image" style="max-width:250px; max-height:250px;" />';
		}
		
	}
	
/*
	* BBcode Youtube
	* @since 2.1
	* @param (Video Id String)
*/
	function bbcode_youtube(string $video_id):string{

		try{
			$API_Key    = Config::get('youtube/api_key');
			$url = "https://www.googleapis.com/youtube/v3/videos?id=".$video_id. "&key=".$API_Key."&part=snippet,contentDetails,statistics,status";
			$json = file_get_contents($url);
			$getData = json::decode($json, true);


			if ($getData['pageInfo']['totalResults'] == 1) {
				$title = $getData['items'][0]['snippet']['title'];
				$image = $getData['items'][0]['snippet']['thumbnails']['standard']['url'];
				$description = $getData['items'][0]['snippet']['description'];


				$descriptionArr = explode(' ', makeASCII($description));
				$description = '';
				for ($x = 0; $x < count($descriptionArr); $x++) {
					if (strlen($descriptionArr[$x]) >= 20)
						$description .= wordwrap($descriptionArr[$x], 40, "<br />\n", true) . ' ';
					else
						$description .= $descriptionArr[$x] . ' ';
				}

				if (strlen($description) >= 150)
					$description = substr($description, 0, 150) . '...';

				$str = '	<div class="container_yt" data-id="' . $video_id . '">
								<div class="preview_yt">
									<img alt="" class="thumb_yt" src="' . $image . '" />
										<img alt="" class="play_yt" src="data:image/png;base64,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"/>
								</div>
								<div class="info_yt">
									<strong>
										<a href="http://youtube.com/watch?v=' . $video_id . '" onclick="window.open(\'http://youtube.com/watch?v=' . $video_id . '\',\'new\',\'\');return false"  class="youtube_link">
										' .  $title . '</a>
									</strong>
								</div>
								<div class="info-small_yt">' . $description . '</div>
								<div style="clear: both;"></div></div>';

				return $str;
			}
			return '<div style="color:red;margin-left:auto;margin-right:auto;">Video cannot be found!!!</div>';
		}
		catch (Exception $e) {
			return '<div style="color:red;margin-left:auto;margin-right:auto;">Video cannot be found!!!</div>';
		}
	}
?>