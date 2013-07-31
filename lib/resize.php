<?php
/**
* Homebrew Website of Claus Beerta
*
* PHP Version 5.3
*
* Copyright (C) <year> by <copyright holders>
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/

/**
* Original version:
*     written by Jarrod Oberto
*     taken from:
*     http://net.tutsplus.com/tutorials/php/image-resizing-made-easy-with-php/
* 
* Example usage:
*     include("classes/Resize.php");
*     $resizer = new Resize('images/cars/large/input.jpg');
*     $resizer->resizeImage(150, 100, 0);
*     $resizer->saveImage('images/cars/large/output.jpg', 100);
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/

Class Resize
{
    private $_image;
    private $_width;
    private $_height;
    private $_imageResized;

    /**
    * Constructor. Open Image and find sizes
    *
    * @param string $fileName Filename to open
    *
    * @return void
    **/
    public function __construct($fileName)
    {
        $this->_image    = $this->_openImage($fileName);
        $this->_width    = imagesx($this->_image);
        $this->_height   = imagesy($this->_image);
    }

    /**
    * Open image
    *
    * @param string $file File to open
    *
    * @return void
    **/
    private function _openImage($file)
    {
        switch(pathinfo($file, PATHINFO_EXTENSION)) {
        case 'jpg':
        case 'jpeg': 
            return imagecreatefromjpeg($file);
        case 'gif':	 
            return imagecreatefromgif($file);
        case 'png':	 
            return imagecreatefrompng($file);
        }

        throw new Exception("Invalid image extension for {$file}.");
    }

    /**
    * Resize The Image to new size
    *
    * @param int    $newWidth  new Width
    * @param int    $newHeight new Height
    * @param string $option    how to scale
    *
    * @return void
    **/
    public function resizeImage($newWidth, $newHeight, $option = 'auto')
    {
        list($width, $height) = $this->_getDimensions(
            $newWidth, 
            $newHeight, 
            $option
        );

        $this->_imageResized = imagecreatetruecolor($width, $height);
        
        imagecopyresampled(
            $this->_imageResized, 
            $this->_image, 
            0, 
            0, 
            0, 
            0, 
            $width, 
            $height, 
            $this->_width, 
            $this->_height
        );

        if ($option == 'crop') {
            $this->_crop($width, $height, $newWidth, $newHeight);
        }
    }

    /**
    * Find dimensions for target
    *
    * @param int    $width  target width
    * @param int    $height target height
    * @param string $option how to scale
    *
    * @return void
    **/
    private function _getDimensions($width, $height, $option)
    {
        switch ($option) {
        case 'portrait':
            return array($this->_getSizeByFixedHeight($height), $height);
        case 'landscape':   
            return array($width, $this->_getSizeByFixedWidth($width));
        case 'auto':        
            return $this->_getSizeByAuto($width, $height);
        case 'crop':        
            return $this->_getOptimalCrop($width, $height);
        case 'exact':
        default:
            return array($width, $height);
        }
    }

    /**
    * Find size by fixed height
    *
    * @param int $height target height
    *
    * @return void
    **/
    private function _getSizeByFixedHeight($height)
    {
        return ($this->_width / $this->_height) * $height;
    }

    /**
    * Find size by fixed width
    *
    * @param int $width target width
    *
    * @return void
    **/
    private function _getSizeByFixedWidth($width)
    {
        return ($this->_height / $this->_width) * $width;
    }

    /**
    * Find size auto
    *
    * @param int $width  target width
    * @param int $height target height
    *
    * @return void
    **/
    private function _getSizeByAuto($width, $height)
    {
        if ($this->_height < $this->_width) {
            return array($width, $this->_getSizeByFixedWidth($width));
        }

        if ($this->_height > $this->_width) {
            return array($this->_getSizeByFixedHeight($height), $height);
        }

        if ($height < $width) {
            return array($width, $this->_getSizeByFixedWidth($width));
        }

        if ($height > $width) {
            return array($this->_getSizeByFixedHeight($height), $height);
        }

        return array($width, $height);
    }

    /**
    * Find Size that doesn't drop below original size
    *
    * @param int $newWidth  target width
    * @param int $newHeight target height
    *
    * @return void
    **/
    private function _getSizeBySafe($newWidth, $newHeight) 
    {
        if ($newWidth >= $this->_width && $newHeight >= $this->_height) {
            //return the original
            return array($this->_width, $this->_height);
        }
        
        if ($newWidth >= $this->_width && $newHeight <= $this->_height ) {
            //height bound
            return array($this->_getSizeByFixedHeight($newHeight), $newHeight);
        }
        
        if ($newHeight >= $this->_height && $newWidth <= $this->_width ) {
            //width bound
            return array($newWidth, $this->_getSizeByFixedWidth($newWidth));
        }
        
        return $this->_getSizeByAuto($newWidth, $newHeight);
    }
      
    /**
    * Find crop auto
    *
    * @param int $width  target width
    * @param int $height target height
    *
    * @return void
    **/
    private function _getOptimalCrop($width, $height)
    {
        $ratio = min($this->_height / $height, $this->_width / $width);
        return array(
            $this->_width / $ratio, 
            $this->_height / $ratio
        );
    }

    /**
    * Crop the image
    *
    * @param int $optimalWidth  target width
    * @param int $optimalHeight target height
    * @param int $width         target width
    * @param int $height        target height
    *
    * @return void
    **/
    private function _crop($optimalWidth, $optimalHeight, $width, $height)
    {
        $x = ($optimalWidth  / 2) - ($width / 2);
        $y = ($optimalHeight / 2) - ($height / 2);

        $crop = $this->_imageResized;

        $this->_imageResized = imagecreatetruecolor($width, $height);
        
        imagecopyresampled(
            $this->_imageResized, 
            $crop, 
            0, 
            0, 
            $x, 
            $y, 
            $width, 
            $height, 
            $width, 
            $height
        );
    }

    /**
    * Return Dimensions of the Original Image
    *
    * @return array
    **/
    public function dimensions()
    {
        return array(
            'width' => $this->_width,
            'height' => $this->_height,
        );
    }

    /**
    * Add subtitle to resized Image 
    *
    * @param string $text text to add
    *
    * @return void
    **/
    public function addText($text)
    {
        $bg = imagecolorallocatealpha($this->_imageResized, 0, 0, 0, 40);
        $white = imagecolorallocatealpha($this->_imageResized, 255, 255, 255, 10);
        
        imagefilledrectangle(
            $this->_imageResized, 
            0, 
            360, 
            $this->_width, 
            $this->_height, 
            $bg
        );
        $font = __DIR__ . '/../public/VeraSe.ttf';
        
        imagettftext(
            $this->_imageResized, 
            13, 
            0, 
            10, 
            378, 
            $white, 
            $font, 
            $text
        );
    }

    /**
    * Save image 
    *
    * @param string $savePath     target
    * @param int    $imageQuality target quality
    *
    * @return void
    **/
    public function saveImage($savePath, $imageQuality="92")
    {
        switch(pathinfo($savePath, PATHINFO_EXTENSION)) {

        case 'jpg':
        case 'jpeg':
            if (imagetypes() & IMG_JPG) {
                imagejpeg($this->_imageResized, $savePath, $imageQuality);
            }
            break;

        case 'gif':
            if (imagetypes() & IMG_GIF) {
                imagegif($this->_imageResized, $savePath);
            }
            break;
        
        case 'png':
            if (imagetypes() & IMG_PNG) {
                // Scale quality from 0-100 to 0-9
                // Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - round(($imageQuality/100) * 9);
                imagepng($this->_imageResized, $savePath, $invertScaleQuality);
            }
            break;
        }

        imagedestroy($this->_imageResized);
    }
}
