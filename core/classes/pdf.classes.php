<?php
/*
	* PDF Module
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* PDF Class
	* @Since 4.5.1
*/

declare(strict_types=1);


use Dompdf\Dompdf;

class pdf
{

    private $_pdf, $_path,$_html;
    private $_pages = 1;
    public $_setpages = 1;


    public function __construct()
    {
        $this->_pdf = new Dompdf();
    }

    public function SetPages($pages):void
    {
        $this->_setpages =$pages;
    }

    public function AddPage($html):void
    {
        $this->_html .= $html;
        if($this->_pages < $this->_setpages)
            $this->_html .= '<div style="page-break-before: always;"></div>';

        $this->_pages++;
    }

    public function Print($path):void
    {
        $this->_pdf->loadHtml($this->_html);

        if(filesystem::createFolderPath($path)){
            if(filesystem::_exist($path))
                filesystem::_rmfile($path);

            $this->_path = $path;
            $this->_pdf->render();
            file_put_contents($this->_path, $this->_pdf->output());
        }
    }

    public function Email($options):mixed
    {
        $mail = new Email();
        $mail->Content_Type('html');
        $mail->setFrom(Config::get('emailer/from'), 'Emailer');

        foreach(explode(',',$options['email']) as $email){
            $mail->addAddress($email);
        }

        $mail->AddAttachment($this->_path,$options['filename'],'application/pdf');
        $mail->Subject($options['subject']);

        $mail->Body($options['message']);

        $mail->send();
    }
}
