<?php
/*
	* PDF Module
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* Zoom
	* @Since 4.5.1
*/

declare(strict_types=1);

//require_once('libs/tcpdf/tcpdf.php');

use Dompdf\Dompdf;

class pdf
{

    private $_pdf, $_path,$_pages,$_html;


    public function __construct($options = [])
    {
        $this->_pdf = new Dompdf();
    }

    public function AddPage($html):void
    {
        $this->_html .= $html;
        if($this->_pages > 1)
            $this->_html .= '<div style="page-break-before: always;"></div>';

        $this->_pages++;
    }

    public function Print($path):void
    {
        $this->_pdf->loadHtml($this->_html);

        if(file::createFolderPath($path)){
            if(file::_exist($path))
                file::_rmfile($path);

            $this->_path = $path;
            $this->_pdf->render();
            file_put_contents($this->_path, $this->_pdf->output());
        }
    }

    public function Email($options):bool
    {
        $mail = new Email;
        $mail->setFrom('reminder@houstoncounty.org', 'Emailer');
        $mail->addAddress($options['email'], $options['emailname']);
        $mail->Subject($options['subject']);
        $mail->AddAttachment($this->_path);
        $mail->Body($options['message']);
        return $mail->send();


        // $fileatt_type = "application/pdf";
        // $fileatt_name = basename($this->_path);
        // $file = fopen($this->_path, 'rb');
        // $data = fread($file, filesize($this->_path));
        // fclose($file);

        // $message = '';
        // $semi_rand = md5(cast::_string(time()));
        // $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        // $headers = "From: " . $options['from'];
        // $headers .= "\rMIME-Version: 1.0\r" .
        //     "Content-Type: multipart/mixed;\r" .
        //     " boundary=\"{$mime_boundary}\"";
        // $message .= "This is a multi-part message in MIME format.\r\r" .
        //     "--{$mime_boundary}\r" .
        //     "Content-Type:text/plain; charset=\"iso-8859-1\"\r" .
        //     "Content-Transfer-Encoding: 7bit\r\r";
        // $message .= $options['message'];
        // $data = chunk_split(base64_encode($data));
        // $message .= "--{$mime_boundary}\r" .
        //     "Content-Type: {$fileatt_type};\r" .
        //     " name=\"{$fileatt_name}\"\r" .
        //     "Content-Transfer-Encoding: base64\r\r" .
        //     $data .= "\r\r" .
        //     "--{$mime_boundary}--\r";

       // $sent = mail($options['to'], $options['subject'], $options['message'], $headers);

        //return cast::_bool($sent);
    }
}
