<?php
/*
	* Zoom
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* Zoom
	* @Since 4.5.1
*/

declare(strict_types=1);

require_once('libs/tcpdf/tcpdf.php');

class pdf
{

    const PDF_MARGIN_LEFT = 15;
    const PDF_MARGIN_RIGHT = 15;
    const PDF_MARGIN_BOTTOM = 25;
    const WIDTH = '800px';
    const HEIGHT = '100%';

    private $_pdf, $_path;


    public function __construct($options = [])
    {

        $pagelayout = array(self::WIDTH, self::HEIGHT);
        $this->_pdf = new TCPDF('p', 'px', $pagelayout, true, 'UTF-8', false);

        // set document information
        $this->_pdf->SetCreator(Config::get('pdf/creator'));
        $this->_pdf->SetAuthor(Config::get('pdf/creator'));
        $this->_pdf->SetTitle('Job Application');
        $this->_pdf->SetSubject('Job Application');
        $this->_pdf->SetKeywords('Job Application');

        $this->_pdf->setPrintHeader(false);
        $this->_pdf->setPrintFooter(false);
        $this->_pdf->SetMargins(self::PDF_MARGIN_LEFT, 0, self::PDF_MARGIN_RIGHT);

        //set auto page breaks
        $this->_pdf->SetAutoPageBreak(TRUE, self::PDF_MARGIN_BOTTOM);

        //set some language-dependent strings
        $this->_pdf->setLanguageArray('English');
    }

    public function AddPage($html)
    {

        $this->_pdf->AddPage();
        $this->_pdf->writeHTML($html, true, false, true, false, '');
    }

    public function Print($path)
    {
        if(file::_exist($path))
            file::_rmfile($path);

        $this->_path = $path;
        $this->_pdf->Output($this->_path, 'F');
    }

    public function Email($options)
    {
        $fileatt_type = "application/pdf";
        $fileatt_name = basename($this->_path);
        $file = fopen($this->_path, 'rb');
        $data = fread($file, filesize($this->_path));
        fclose($file);

        $message = '';
        $semi_rand = md5(cast::_string(time()));
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        $headers = "From: " . $options['from'];
        $headers .= "\rMIME-Version: 1.0\r" .
            "Content-Type: multipart/mixed;\r" .
            " boundary=\"{$mime_boundary}\"";
        $message .= "This is a multi-part message in MIME format.\r\r" .
            "--{$mime_boundary}\r" .
            "Content-Type:text/plain; charset=\"iso-8859-1\"\r" .
            "Content-Transfer-Encoding: 7bit\r\r";
        $message .= $options['message'];
        $data = chunk_split(base64_encode($data));
        $message .= "--{$mime_boundary}\r" .
            "Content-Type: {$fileatt_type};\r" .
            " name=\"{$fileatt_name}\"\r" .
            "Content-Transfer-Encoding: base64\r\r" .
            $data .= "\r\r" .
            "--{$mime_boundary}--\r";

        $sent = mail($options['to'], $options['subject'], $options['message'], $headers);


        return cast::_bool($sent);
    }
}
