<?php
/*
	* Barcode Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Barcode
	* @Since 4.1.3
*/

declare(strict_types=1);
class barcode
{
    private $options;
    private $type = 'png';
    private $code_type = 'code128';
    private $size = 30;
    private $factor = 1;
    private $data = 'test';
    private $footer = true;
    private $view = true;
    private $save = false;
    private $path = '/content/uploads/';
    private $bgcolor = 'FFFFFF';
    private $fgcolor = '000000';

    public function __construct($data)
    {
        $this->data    = $data;
    }

    public function _setOptions(object $options):void
    {
        $this->options = $options;
        if (property_exists($this->options, 'type'))
            $this->type = $this->options->type;

        if (property_exists($this->options, 'code_type'))
            $this->code_type = $this->options->code_type;

        if (property_exists($this->options, 'fgcolor'))
            $this->fgcolor = $this->options->fgcolor;

        if (property_exists($this->options, 'bgcolor'))
            $this->bgcolor = $this->options->bgcolor;

        if (property_exists($this->options, 'footer'))
            $this->footer = $this->options->footer;

        if (property_exists($this->options, 'size'))
            $this->size = $this->options->size;

        if (property_exists($this->options, 'factor'))
            $this->factor = $this->options->factor;

        if (property_exists($this->options, 'save'))
            $this->save = $this->options->save;

        if (property_exists($this->options, 'view'))
            $this->view = $this->options->view;

        if (property_exists($this->options, 'path'))
            $this->path = $_SERVER["DOCUMENT_ROOT"] . $this->options->path;
        else
            $this->path = $_SERVER["DOCUMENT_ROOT"] . $this->path;
    }

    public function render():string
    {
        $image = $this->encode();
        ob_start("callback");
        switch (str::_tolower($this->code_type)) {
            case "qrcode":
                $this->qrcode_encode();
                break;
            default:
                switch (strtolower(preg_replace('/[^A-Za-z0-9]/', '', $this->type))) {
                    case 'gif':
                        header('Content-Type: image/gif');
                        if ($this->save)
                            imagegif($image, $this->path . hash::generateRandomString(25) . '.gif');
                        if ($this->view)
                            imagegif($image);

                        imagedestroy($image);
                        break;
                    case 'jpg':
                    case 'jpeg':
                        header('Content-Type: image/jpeg');
                        if ($this->save)
                            imagejpeg($image, $this->path . hash::generateRandomString(25) . '.jpg');
                        if ($this->view)
                            imagejpeg($image);

                        imagedestroy($image);
                        break;
                    case 'png':
                    default:
                        header('Content-Type: image/png');
                        if ($this->save)
                            imagepng($image, $this->path . hash::generateRandomString(25) . '.png');
                        if ($this->view)
                            imagepng($image);

                        imagedestroy($image);
                        break;
                }
                break;
        }
        $outputimage = ob_get_contents();
        ob_end_clean();

        return $outputimage;
    }


    private function encode():GdImage
    {
        $code_string = '';
        switch (str::_tolower($this->code_type)) {
            case "code128":
            case "code128b":
                $code_string =  $this->code_128_encode();
                break;
            case "code128a":
                $code_string =  $this->code_128_a_encode();
                break;
            case "code39":
                $code_string =  $this->code_39_encode();
                break;
            case "code25":
                $code_string =  $this->code_25_encode();
                break;
            case "codabar":
                $code_string =  $this->codabar_encode();
                break;
        }

        // Pad the edges of the barcode
        $code_length = 20;
        $text_height = $this->footer ? 18 : 0;

        for ($i = 1; $i <= strlen($code_string); $i++) {
            $code_length = $code_length + cast::_int(substr($code_string, ($i - 1), 1));
        }

        $img_width = $code_length * $this->factor;
        $img_height = $this->size;

        $image = imagecreate($img_width, cast::_int($img_height + $text_height));

        $bgcolour = self::hexToRgb($this->bgcolor);
        $fgcolor = self::hexToRgb($this->fgcolor);

        $backgroundColour = imagecolorallocate($image, $bgcolour['r'],  $bgcolour['g'],  $bgcolour['b']);
        $textColour = imagecolorallocate($image, $fgcolor['r'],  $fgcolor['g'],  $fgcolor['b']);

        imagefill($image, 0, 0, $backgroundColour);

        if ($this->footer)
            imagestring($image, 5, 31, $img_height, $this->data, $textColour);

        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++) {
            $cur_size = $location + (substr($code_string, ($position - 1), 1));
            imagefilledrectangle($image, $location * $this->factor, 0, $cur_size * $this->factor, $img_height, ($position % 2 == 0 ? $backgroundColour : $textColour));
            $location = $cur_size;
        }

        return $image;
    }

    private function code_128_encode(): string
    {
        $data = preg_replace('/[\x80-\xFF]/', '', $this->data);

        $code_string = "";
        $chksum = 104;
        $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
        $code_keys = array_keys($code_array);
        $code_values = array_flip($code_keys);
        for ($X = 1; $X <= strlen($data); $X++) {
            $activeKey = substr($data, ($X - 1), 1);

            $code_string .= $code_array[$activeKey];
            $chksum = ($chksum + ($code_values[$activeKey] * $X));
        }

        $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

        $code_string = "211214" . $code_string . "2331112";
        return $code_string;
    }

    private function code_128_a_encode():string
    {
        $data = preg_replace('/[\x80-\xFF]/', '', $this->data);
        $code_string = '';
        $chksum = 103;
        $text = str::_toupper($data);
        $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
        $code_keys = array_keys($code_array);
        $code_values = array_flip($code_keys);
        for ($X = 1; $X <= strlen($text); $X++) {
            $activeKey = substr($text, ($X - 1), 1);
            $code_string .= $code_array[$activeKey];
            $chksum = ($chksum + ($code_values[$activeKey] * $X));
        }
        $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

        $code_string = "211412" . $code_string . "2331112";
        return $code_string;
    }

    private function code_39_encode():string
    {
        $data = preg_replace('/[\x80-\xFF]/', '', $this->data);
        $code_string = '';
        $code_array = array("0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211");

        $upper_text = strtoupper($data);
        for ($X = 1; $X <= strlen($upper_text); $X++) {
            $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
        }

        $code_string = "1211212111" . $code_string . "121121211";
        return $code_string;
    }

    private function code_25_encode():string
    {
        $data = preg_replace('/[\x80-\xFF]/', '', $this->data);
        $code_string = '';
        $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
        $code_array2 = array("3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1");

        for ($X = 1; $X <= strlen($data); $X++) {
            for ($Y = 0; $Y < count($code_array1); $Y++) {
                if (substr($data, ($X - 1), 1) == $code_array1[$Y])
                    $temp[$X] = $code_array2[$Y];
            }
        }

        for ($X = 1; $X <= strlen($data); $X += 2) {
            if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                $temp1 = explode("-", $temp[$X]);
                $temp2 = explode("-", $temp[($X + 1)]);
                for ($Y = 0; $Y < count($temp1); $Y++)
                    $code_string .= $temp1[$Y] . $temp2[$Y];
            }
        }

        $code_string = "1111" . $code_string . "311";
        return $code_string;
    }

    private function codabar_encode():string
    {
        $data = preg_replace('/[\x80-\xFF]/', '', $this->data);
        $code_string = '';
        $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D");
        $code_array2 = array("1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221");

        // Convert to uppercase
        $upper_text = str::_toupper($data);

        for ($X = 1; $X <= strlen($upper_text); $X++) {
            for ($Y = 0; $Y < count($code_array1); $Y++) {
                if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y])
                    $code_string .= $code_array2[$Y] . "1";
            }
        }
        $code_string = "11221211" . $code_string . "1122121";
        return $code_string;
    }

    private function qrcode_encode():void
    {
        if ($this->save)
            QRcode::png($this->data, $this->path . hash::generateRandomString(25) . '.png');

        if ($this->view)
            QRcode::png($this->data);
    }


    public static function hexToRgb(string $hex):array
    {
        $hex      =  preg_replace('/[^0-9A-Fa-f]/', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return $rgb;
    }
}
