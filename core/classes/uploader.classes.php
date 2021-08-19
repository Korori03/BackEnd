<?php
/*
	* Uploader Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Uploader Class
	* @since 4.0.0
*/
declare(strict_types=1);
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('memory_limit', '500M');

class Uploader{

/*
	* Private Variables
	* @since 4.0.0	
*/
	private static
	 				$_errors = array(),
					$_upload_dir		=	"/content/uploads/",
					$_upload_temp_dir	=	"/content/uploads/temp/";

/*
	* Clear File if Exist
	* @since 4.0.0
	* @param (String FileName)
*/
	public static function ajax_clear_file(string $file_name){
		$file_temp_path     = getcwd() . self::$_upload_temp_dir . $file_name;
		@unlink($file_temp_path);
	}

/*
	* Move File to Correct Location
	* @since 4.0.0
	* @param (String FileName,String NewName)
*/
	public static function ajax_move_file(string $file_name,string $pre_file){
		
		$file_temp_path     = getcwd() .self::$_upload_temp_dir . $file_name;
		
		$path_parts 		= pathinfo($file_temp_path);

		$file_path     		= getcwd() . self::$_upload_dir . $pre_file.'_'.Slug::_url($path_parts['filename']).'.'.$path_parts['extension'];
		
		if(rename($file_temp_path, $file_path))
			@unlink($file_temp_path);
		
		echo Json::returnJson(true, self::$_upload_dir . $pre_file.'_'.Slug::_url($path_parts['filename']).'.'.$path_parts['extension']);
	}

/*
	* Upload File
	* @since 4.0.0
	* @param (String File,Object FileData)
*/
	public static  function ajax_upload_file(string $file,mixed $file_data){

		$file_path     = getcwd() .self::$_upload_temp_dir.$file;

		$file_data     = self::decode_chunk( $file_data );

		if ( false === $file_data ) {
			echo 'false';
			exit();
		}

		file_put_contents( $file_path, $file_data,FILE_APPEND);
		echo 'true';
	}


/*
	* Decode Chuck
	* @since 4.0.0
	* @param (Object Data)
*/
	private static function decode_chunk(mixed $data ) {
		$data = explode( ';base64,', $data );

		if (!is_array( $data ) || !isset($data[1]) )
			return false;

		$data = base64_decode( $data[1] );
		if (!$data)
			return false;

		return $data;
	}

/*
	* Resize image
	* @since 4.0.0
	* @param (String File Input Name, Integer Width, String Filename,Integer Quality, Integer Height)
*/
	public function resizeImage(string $pathToImage,int $imagewidth,string $filename,int $quality = 100,string $heighttype = 'auto' ){
		$ext =  file::_extension($filename);
		
		if($ext === 'png')
			$img = imagecreatefrompng( "{$pathToImage}{$filename}" );
		else if($ext === 'gif')
			$img = imagecreatefromgif( "{$pathToImage}{$filename}" );
		else
			$img = imagecreatefromjpeg( "{$pathToImage}{$filename}" );

		
		$width = imagesx( $img );
		$height = imagesy( $img );

		$new_width = $imagewidth;

		if($heighttype == 'auto')
			$new_height = cast::_int(floor( $height * ( $imagewidth / $width ) ));
		else
			$new_height = cast::_int($heighttype);
			
		if($imagewidth == 'auto')
			$new_width = cast::_int(floor( $width * ( $heighttype / $height ) ));
		else
			$new_width = cast::_int($imagewidth);
			
			
		$tmp_img = imagecreatetruecolor( $new_width, $new_height );
		
		imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		if($ext === 'png')
			imagepng( $tmp_img, "{$pathToImage}{$filename}",9);
		else if($ext === 'gif')
			imagegif( $tmp_img, "{$pathToImage}{$filename}",$quality );
		else
			imagejpeg( $tmp_img, "{$pathToImage}{$filename}",$quality );
	}
	
/*
	* Create Thumb Image
	* @since 4.0.0	
	* @param (String File Path to Image, String File Path to Thumb,Integer Width, String Filename)
*/
	public function createThumbs(string $pathToImage,string  $pathToThumb,int $thumbWidth,string $filename ) {
		$ext = file::_extension($filename);

		if($ext === 'png')
			$img = imagecreatefrompng( "{$pathToImage}{$filename}" );
		else if($ext === 'gif')
			$img = imagecreatefromgif( "{$pathToImage}{$filename}" );
		else
			$img = imagecreatefromjpeg( "{$pathToImage}{$filename}" );
		
		
		$width = imagesx( $img );
		$height = imagesy( $img );

		$new_width = $thumbWidth;
		$new_height = cast::_int(floor( $height * ( $thumbWidth / $width ) ));

		$tmp_img = imagecreatetruecolor( $new_width, $new_height );
		
		imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		if($ext === 'png')
			imagepng( $tmp_img, "{$pathToThumb}{$filename}" );
		else if($ext === 'gif')
			imagegif( $tmp_img, "{$pathToThumb}{$filename}" );
		else
			imagejpeg( $tmp_img, "{$pathToThumb}{$filename}" );
		
	
	}
/*
	* Remove File
	* @since 4.0.0	
	* @param (String FilePath)
*/	
	public static function removeFile(string $path):void{
		if(file_exists($path)){	
			$fh = fopen($path, 'w');	
			fclose($fh);
			unlink($path);
		}
	}	


/*
	* Create Guid
	* @since 4.0.0	
	* @param (String NameSpace)
*/
	public function create_guid(mixed $namespace = '') {
		static $guid = '';
		$uid = uniqid("", true);
		$data = $namespace;
		$data .= $_SERVER['REQUEST_TIME'];
		$data .= $_SERVER['HTTP_USER_AGENT'];
		$data .= $_SERVER['LOCAL_ADDR'];
		$data .= $_SERVER['LOCAL_PORT'];
		$data .= $_SERVER['REMOTE_ADDR'];
		$data .= $_SERVER['REMOTE_PORT'];
		$data .= time();
		
		$hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
		$guid = substr($hash,  0,  8) .
				substr($hash,  8,  4) .
				substr($hash, 12,  4) .
				substr($hash, 16,  4) .
				substr($hash, 20, 12);
		return $guid;
	 }


/*
	* Type Extension
	* @since 4.0.0
	* @param (String Path)
*/	
	public function typeExt(string $path) {
		$extension =  strtolower(file::_extension($path));
		$type ='file';
		if($extension === 'png' || $extension === 'gif' || $extension === 'jpg' || $extension === 'jpeg'){
			$type = 'image';
		}
		return $type;	
	}

	/*
		* File Upload
		* @since 4.0.0	
		* @param (String File Input Name, String Folder, String Types, Integer Size, Integer Width, String Optional, Integer Height)
	*/
	public static function fileUpload(mixed $file_id,string $folder="/content/uploads/",string $types="",int $sizelimit = 20) {
			
		$file_name = '';	
		$query = false;
		
		if(!$_FILES[$file_id]['name']) return array($query,'No file specified','');
			$file_title = $_FILES[$file_id]['name'];
	
		$extension =  explode('.',$file_title);
		$extension = strtolower(end($extension));
		
		$file_name = time().'_'. md5($_FILES[$file_id]['name'])  . '.' . $extension;
		$all_types = explode(",",strtolower($types));
		
		if($types) {
			if(!in_array($extension,$all_types)){
				self::addError("This is not a valid file.");
				self::removeFile($folder . $file_name);
				return array($query,self::errors(),'');
			}
		}
		
		self::removeFile($folder . $file_name);
		$size = filesize($_FILES[$file_id]['tmp_name']);
		
		if($size > ($sizelimit * 1092821)){
			self::addError("Cannot upload the file: File is to large of size");
			
		}else if (!move_uploaded_file($_FILES[$file_id]['tmp_name'], $folder .$file_name)) {
			if(!file_exists($folder))
				self::addError("Cannot upload the file: Folder does not exist.");
			else if(!is_writable($folder))
				self::addError("Cannot upload the file: Folder is not writable.");
			else if(!is_writable($folder .$file_name)) 
				self::addError("Cannot upload the file: File is not writable.");
			$file_name = '';
		}
		else {
			if(!$_FILES[$file_id]['size']) { 
				self::removeFile($folder . $file_name);
				self::addError("Empty file found - please use a valid file.");
			}
			else{
				$query = true;
			}
		}
		return array($query,self::$_errors,$file_name);
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
?>