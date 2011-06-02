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

if ( ! defined('LIMONADE') ) {
    exit('No direct script access allowed');
}

/**
* Debugging shortcut function
*
* @param string $message Message to log
* 
* @return void
**/
function d($message)
{
    if (!is_string($message)) {
        $message = print_r($message, true);
    }
    
    if ( class_exists("WebServer", false) ) {
        WebServer::log($message);
    } else {
        error_log($message);
    }
}

/**
* Return a Random Header Image
*
* @param string $image_dir Directory under $public_dir with images
*
* @return void
**/
function randomHeaderImage($image_dir)
{
    $dir = option('public_dir') . '/' .  $image_dir;
    $glob = "{{$dir}*.jpg, {$dir}*.png}";
    
    $files = array();
    foreach (glob($glob, GLOB_BRACE) as $filename) {
        $files[] = $filename;
    }

    mt_srand((double)microtime()*1000000); // seed for PHP < 4.2
    $rand = mt_rand(0, count($files) - 1); // $i was incremented as we went along

    return basename($files[$rand]);
}

/**
* Format a DateTime / String for display
*
* @param string $date Date to Format
*
* @return void
**/
function formatDate($date)
{
    $date = new DateTime($date);
    
    if ( !$date ) {
        return $date;
    }
    
    return $date->format(option('date_format'));
}


