<?php
/**
 * Adding text over image
 * Writes the given text with a border into the image using</span> TrueType fonts.
 *
 */
class TextOverImage{
    private $write_height;
    private $image;
    private $font_static_size = 0;
    private $font_file = "/srv/www/myzamana.com/assets/fonts/arial.ttf";
    private $text_border_size = 4; //In Pixels
    private $line_gap = 6; //In Pixels
    private $height_to_write = 0.5; // % of height to write the text on 0.5 => 50%
    private $minimum_font_thickness = 25;
    private $divided_string_array = array();
    private $original_title;
    private $font_txt_width;
    private $font_txt_height;
    private $parameters_set = FALSE;
    private $max_line_length;
    

    public function start_image_processing($title, $inputImgPath = NULL, $outputImgPath = NULL){
        $title = trim($title);
        if(!$title)
            return False;
        $this->original_title = $title;
        if(!$inputImgPath)
            $inputImgPath = '/root/test.jpg';
        if(!$outputImgPath)
            $outputImgPath = '/root/test1.jpg';
        $this->image = imagecreatefromjpeg($inputImgPath);
        if(!$this->image)
            return False;
        $this->set_appropriate_font_size_and_divide_text($title);
        $this->write_divided_text_over_string();
        imagejpeg($this->image, $outputImgPath);
        return True;
    }

    // Adds border to text
    private function imagettfstroketext(&$image, $size, $angle, $x, $y, &$textcolor, &$strokecolor, $fontfile, $text, $px) {
        //  @author John Ciacia 
        for($c1 = ($x-abs($px)); $c1 <= ($x+abs($px)); $c1++)
            for($c2 = ($y-abs($px)); $c2 <= ($y+abs($px)); $c2++)
                $bg = imagettftext($image, $size, $angle, $c1, $c2, $strokecolor, $fontfile, $text);

       return imagettftext($image, $size, $angle, $x, $y, $textcolor, $fontfile, $text);
    }

    private function calculate_font_information($title, $image){
        $img_width = imagesx($image);
        $img_height = imagesy($image);
        // error_log("\nIn TOI $img_height, w: $img_width\n",3,"/var/tmp/TOI.log");

        // find font-size for $txt_width = 80% of $img_width...
        $fontSize = 1;
        $txt_max_width = intval(0.8 * $img_width);
        $font_file = $this->font_file;
         do {
            $fontSize++;
            //echo "\n$fontSize, $font_file, $title\n";
            $p = imagettfbbox($fontSize,0,$font_file,$title);
            if(!$p || $fontSize > 300)
                return False;
            $txt_width=$p[2]-$p[0];
            $txt_height=$p[1]-$p[7]; // just in case you need it

        } while ($txt_width <= $txt_max_width && $txt_height < (0.35 * $img_height));
        return array("font_size" => $fontSize, "txt_height" => $txt_height, "txt_width" => $txt_width);
    }

    private function adjust_height_to_write_percentage($no_lines){
        if($no_lines <=1 )
            $this->height_to_write = 0.6;
        else if($no_lines < 5){
            $this->height_to_write = 0.5;
        }else if($no_lines <= 7)
            $this->height_to_write = 0.2;
        else
            $this->height_to_write = 0.09;
    }

    private function write_string_over_image($image, $txt_width, $fontSize, $title){
        $img_width = imagesx($image);
        $img_height = imagesy($image);
        $y = $this->write_height;
        $x = ($img_width - $txt_width) / 2;
        $font_file = $this->font_file;
        $angle = 0;
        $font_color = imagecolorallocate($image, 255, 255, 255);
        $stroke_color = imagecolorallocate($image, 0, 0, 0);
        $this->imagettfstroketext($image, $fontSize, $angle, $x, $y, $font_color, $stroke_color, $font_file, $title, $this->text_border_size);
    }

    private function set_appropriate_font_size_and_divide_text($title){
        $font_info = $this->calculate_font_information($title, $this->image);
        if(!$font_info)
            return False;
        // if font size not set and fontsize < 25 break it else write it
        if( ($font_info["font_size"] < $this->minimum_font_thickness)  ){
            // Divide string into 90% length 
            $half_len = (int)(strlen($title)*0.9);
            $this->set_appropriate_font_size_and_divide_text(substr($title, 0, $half_len));
        }elseif(!$this->parameters_set){
            $this->font_static_size = $font_info["font_size"];
            $this->font_txt_width = $font_info["txt_width"];
            $this->font_txt_height = $font_info["txt_height"];
            $max_line_length = strlen($title);
            $this->max_line_length = $max_line_length;
            $divided_text = wordwrap($this->original_title, $max_line_length);
            $this->divided_string_array = explode("\n", $divided_text);
            $this->adjust_height_to_write_percentage(count($this->divided_string_array));
            $this->write_height = imagesy($this->image) * $this->height_to_write;
            $this->parameters_set = TRUE; 
        }
        return True;
    }

    private function write_divided_text_over_string(){
        foreach ($this->divided_string_array as $title) {
            $title = str_pad($title, $this->max_line_length, " ",STR_PAD_BOTH);
            $this->write_string_over_image($this->image, $this->font_txt_width, $this->font_static_size, $title);
            $this->write_height += $this->font_txt_height + $this->line_gap;
        }    
    }

}
