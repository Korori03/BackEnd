<?php

/*
	* Email Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* Email Class
	* @Since 4.0.0
*/

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email
{

	/*
	* Variables
	* @Since 4.1.0
*/
	public 	$_to 			= 	array(),
		$_to_name 		= 	array(),
		$_Subject 		=	'',
		$_Message		=	'',
		$_eol 			= 	PHP_EOL,
		$_Send_to 		=	'',
		$_Content_Type 	= 	'html',
		$_Host 			= 	'smtp.sendgrid.net',
		$_SMTPAuth 		= 	true,
		$_SMTPSecure	=	'tls',
		$_Port 			= 	587,
		$_isHTML 		= 	true,
		$_ErrorInfo 	= 	'',
		$_ErrorBool 	= 	false,
		$attachment     = 	array(),
		$_mailer		=	'';

	/*
	* Construct Function
	* @Since 4.0.0
	* @Param (String)
*/
	public function __construct()
	{
		$this->_mailer = new PHPMailer(true);
		$this->_mailer->isSMTP();
		$this->_mailer->Host =	$this->_Host;
		$this->_mailer->SMTPAuth = $this->_SMTPAuth;
		$this->_mailer->SMTPSecure =  $this->_SMTPSecure;
		$this->_mailer->Port = $this->_Port;
		$this->_mailer->IsHTML($this->_isHTML);

		$this->_mailer->Username   	= Config::get('emailer/sender/username');
		$this->_mailer->From  		= Config::get('emailer/sender/email');
		$this->_mailer->Password   	= Config::get('emailer/sender/password');

		$this->_to = $this->attachment = $this->_to_name = array();
		$this->_ErrorBool = false;
		$this->_ErrorInfo = $this->_Subject = $this->_Message = $this->_Send_to = '';
	}

	/*
	* Send Function
	* @Since 4.0.0
	* @Param (None)
*/
	public function send()
	{
		try {
			//Loop through Attachments
			for ($x = 0; $x < count($this->attachment); $x++) {
				$this->_mailer->AddAttachment(
					$this->attachment[$x][0],
					$this->attachment[$x][1]
				);
			}

			//Loop through Senders
			if (count($this->_to) > 0) {
				for ($x = 0; $x < count($this->_to); $x++) {
					$this->_mailer->addAddress($this->_to[$x], $this->_to_name[$x]);
				}
			}

			//Attempt to Email
			if (!(bool)$this->_ErrorBool) {

				$this->_ErrorBool = false;

				$this->_mailer->Subject = $this->_Subject;
				$this->_mailer->Body = $this->_Message;
				$this->_mailer->AltBody = 'Plain text message body for non-HTML email client. Gmail SMTP email body.';
				$this->_mailer->send();
				return true;
			}
		} catch (Exception $e) {
			$this->_ErrorInfo .= $this->_mailer->ErrorInfo;
			$this->_ErrorBool = false;
			return false;
		}
	}

	/*
	* Add Attachment
	* @Since 4.0.0
	* @Param (String, String, String)
*/
	public function AddAttachment(string $path, string $name = '', string $type = 'application/octet-stream'): bool
	{
		try {
			if (!is_file($path) && !filesystem::_exist($this->attachment[0])) {
				$this->_ErrorInfo .= 'Unable to Find: ' . $path;
				$this->_ErrorBool = true;
				return true;
			}
			$filename = basename($path);
			if ($name == '')
				$name = $filename;

			$this->attachment[] = array(
				0 => $path,
				1 => $name,
				2 => $type
			);
		} catch (Exception $e) {
			$this->_ErrorInfo .= $e->getMessage();
			$this->_ErrorBool = true;
			return false;
		}
		return true;
	}

	/*
	* Add Address to
	* @Since 4.0.0
	* @Param (String, String)
*/
	public function addAddress(string $to, string $name = ''): void
	{
		array_push($this->_to, $to);

		if ($name == '')
			$name = explode('@', $to)[0];

		array_push($this->_to_name, ucwords($name));
	}

	/*
	* Add Address to Array
	* @Since 4.0.0
	* @Param (Array To)
*/
	public function addAddressArray(array $to): void
	{

		foreach ($to as $key => $name) {
			array_push($this->_to, $key);
			if ($name == '')
				$name = explode('@', $key);

			array_push($this->to_name, ucwords($name));
		}
	}

	/*
	* Add Body
	* @Since 4.0.0
	* @Param (String)
*/
	public function Body(string $Message): void
	{
		$this->_Message = $Message;
	}

	/*
	* Change Content Type
	* @Since 4.0.0
	* @Param (String)
*/
	public function Content_Type(string $type): void
	{
		switch ($type) {
			case 'plain':
				$this->_Content_Type = 'plain';
				break;
			default:
				$this->_Content_Type = 'html';
				break;
		}
	}

	/*
	* Set Subject Line
	* @Since 4.0.0
	* @Param (String)
*/
	public function Subject(string $Subject): void
	{
		$this->_Subject = $Subject;
	}
	/*
	* Get Error Line
	* @Since 4.0.0
	* @Param (String)
*/
	public function Error(): string
	{
		return $this->_ErrorInfo;
	}
	/*
	* Set From Address
	* @Since 4.0.0
	* @Param (String)
*/
	public function setFrom(string $sender, string $name = ''): void
	{
		if ($name == '')
			$name = ucwords(explode('@', $sender)[0]);

		$this->_Sender = $name . " <" . $sender . ">";
	}

	/*
	* Set Host and Port
	* @Since 4.0.1
	* @Param (String)
*/
	public function setOptions(array $options): void
	{
		if(isset($options['host'])){
			$this->_Host 		 	= 	cast::_string($options['host']);
			$this->_mailer->Host 	=	$this->_Host;
		}
		if(isset($options['port'])){
			$this->_Port 			= 	cast::_int($options['port']);
			$this->_mailer->Port 	=	$this->_Port;
		}
		if(isset($options['auth'])){
			$this->_SMTPAuth 			= 	filter::bool($options['auth']);
			$this->_mailer->SMTPAuth 	= 	$this->_SMTPAuth;
		}
		if(isset($options['secure'])){
			$this->_SMTPSecure		= 	cast::_string($options['secure']);
			$this->_mailer->SMTPSecure = $this->_SMTPSecure;
		}
		if(isset($options['html'])){
			$this->_isHTML		= 	filter::bool($options['html']);
			$this->_mailer->IsHTML($this->_isHTML);
		}
	}
}
