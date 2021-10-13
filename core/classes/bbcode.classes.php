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
			'/\[left\](.+?)\[\/left\]/is' => '<p style=\'text-align:left\'>$1</p>',
			'/\[center\](.+?)\[\/center\]/is' => '<p style=\'text-align:center\'>$1</p>',
			'/\[right\](.+?)\[\/right\]/is' => '<p style=\'text-align:right\'>$1</p>',
			'/\[justify\](.+?)\[\/justify\]/is' => '<p style=\'text-align:justify\'>$1</p>',
			'/\[align=(left|right|center)\](.+?)\[\/align\]/is' => '<p style=\'text-align:$1\'>$2</p>',
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
			'/\[video\](.+?)\[\/video\]/is' => '<video height="410" width="600" src="$1" controls="" preload=""></video>',
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
	function bbcode_youtube(string $videoid):string{

		try{	
			$feedURL = 'http://gdata.youtube.com/feeds/api/videos/'.$videoid;
			$curl = curl_init($feedURL);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$data = curl_exec($curl);	

			$entry = simplexml_load_string($data);
			$media = $entry->children('http://search.yahoo.com/mrss/'); 					

			$descriptionTemp =  makeASCII($media->group->description); 
			
			$descriptionArr = explode(' ',$descriptionTemp);
			$description = '';
			for($x = 0;$x < count($descriptionArr);$x++){
				if(strlen($descriptionArr[$x]) >= 20)
					$description .= wordwrap($descriptionArr[$x],40,"<br />\n",true) . ' ';
				else
					$description .= $descriptionArr[$x] . ' ';
			}
			
			if(strlen($description) >= 150)
				$description = substr($description,0,150) . '...';

			$str = '<table><tr style="display:inline;"><td><div class="container_yt" id="yt_'.time() .rand(0,9). rand(0,9).rand(0,9).$videoid.'"><div class="preview_yt"><img alt="" class="thumb_yt" src="http://i.ytimg.com/vi/'.$videoid.'/' .rand(0,2) .'.jpg" /><img alt="" class="play_yt" src="https://s-static.ak.fbcdn.net/rsrc.php/v2/yG/r/Gj2ad6O09TZ.png" /></div><div class="info_yt"><b><a href="http://youtube.com/watch?v='.$videoid.'" onclick="window.open(\'http://youtube.com/watch?v='.$videoid.'\',\'new\',\'\');return false"  class="youtube_link">'.  makeASCII($media->group->title) .'</a></b></div><div class="info-small_yt">' . $description . '</div><div style="clear: both;"></div></div></td></tr></table>';

			return $str;
		}
		catch (Exception $e) {
			return '<div style="color:red;margin-left:auto;margin-right:auto;">Video cannot be found!!!</div>';
		}
	}
?>