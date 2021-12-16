<?php

/*
	* Redirect Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Redirect Class
	* @since 4.0.0
*/

declare(strict_types=1);
class Redirect
{
	protected static $template_dir = '/content/themes/';
	protected static $errors = 'errors';

	/*
	* Redirect To
	* @since 4.0.0	
	* @param (String/int Location)
*/
	public static function to(mixed $location = null): void
	{
		$_error_pages = array(
			'404' => self::$template_dir . Options::get('template') . '/' . self::$errors . '/404.php',
			'500' => self::$template_dir . Options::get('template') . '/' . self::$errors . '/500.php',
			'400' => self::$template_dir . Options::get('template') . '/' . self::$errors . '/400.php',
			'401' => self::$template_dir . Options::get('template') . '/' . self::$errors . '/401.php',
			'403' => self::$template_dir . Options::get('template') . '/' . self::$errors . '/403.php',
		);


		if ($location) {
			if (is_numeric($location)) {
				if (filesystem::_exist($_error_pages[$location])) {
					switch ($location) {
						case 404:
							header('HTTP/1.0 404 Not Found');
							include($_error_pages[$location]);
							exit();
							break;
						case 500:
							header('HTTP/1.0 500 Not Found');
							include($_error_pages[$location]);
							exit();
							break;
						case 400:
							header('HTTP/1.0 400 Not Found');
							include($_error_pages[$location]);
							exit();
							break;
						case 401:
							header('HTTP/1.0 401 Not Found');
							include($_error_pages[$location]);
							exit();
							break;
						case 403:
							header('HTTP/1.0 403 Not Found');
							include($_error_pages[$location]);
							exit();
							break;
					}
				}
			}
			header('Location: ' . $location);
			exit();
		}
	}

	/*
	* Redirect To a File
	* @since 4.0.3
	* @param (String Location)
*/
	public static function file(string $location = null, string $type = 'file', string $name = null): void
	{
		if ($location) {
			if ($name == null)
				$name  = time();

			switch ($type) {
				case 'png':
					header('Content-Type: image/png');
					readfile($location);
					exit();
					break;
				case 'jpg':
					header('Content-Type: image/jpeg');
					readfile($location);
					exit();
					break;
				case 'pdf':
					header('Content-Type: application/pdf');
					include($location);
					exit();
					break;
				default:
					header('Content-Description: File Transfer');
					$fsize = filesize($location);
					$ext = filesystem::_extension($location);
					header('Content-Description: File Transfer');
					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Cache-Control: private", false);
					header("Content-Type: application/force-download");
					header("Content-Transfer-Encoding: binary");
					header("Content-Disposition: attachment; filename=\"" . $name . '.' . $ext . '"');
					header("Content-Length: " . $fsize);
					include($location . '.' . $ext);

					exit();
					break;
			}
		}
	}
}
