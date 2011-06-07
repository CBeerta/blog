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
* @return formatted date
**/
function formatDate($date)
{
    $date = new DateTime($date);
    
    if ( !$date ) {
        return $date;
    }
    
    return $date->format(option('date_format'));
}

/**
* Format content
*
* @param string $content    The content to format
* @param string $line_break What Line Break to use
*
* @return html
**/
function formatContent($content, $line_break='<br />')
{
    if (strstr($content, '<') !== false) {
        /**
        * This is somewhat specific. I dunno if Wordpress generated these.
        * It SUCKS. maybe easier to just clean my posts?
        *
        * Anyhow: Any line ending that is NOT a html tag followed by 2 linebreaks
        * will be converted to two '<br>' tags
        *
        * This seems to convert my posts best, leaving properly formatted ones intact
        * and only alter the ones that need it.
        *
        * FIXME: This has to DIAF! Fix the goddamn content in the database already!
        **/
        $content = preg_replace(
            '#([\w:;.,!\?\(\)]+?)(\r|\n){2,}#', 
            '\1<br><br>', 
            $content
        );
    } else {
        // This is probably Markdown or plaintext
        $content = Markdown($content);
    }

    return $content;
}

/**
* Create a "Slug" from a title
*
* @param string $title   The title to create a slug from
* @param string $sep     A Seperator
* @param string $charset The Charset to use
*
* @return string a slug
**/
function buildSlug($title, $sep = "-", $charset = "UTF-8")
{
    // Build Slug
    $slug = strtolower(htmlentities($title, ENT_COMPAT, $charset));
    $slug = preg_replace(
        '/&(.)(acute|cedil|circ|lig|grave|ring|tilde|uml);/', "$1", 
        $slug
    );
    $slug = preg_replace(
        '/([^a-z0-9]+)/', 
        $sep, 
        html_entity_decode($slug, ENT_COMPAT, $charset)
    );
    $slug = trim($slug, $sep);
    
    return $slug;
}

/**
* Check if the Client is allowed to edit
*
* @TODO This should obviously be a bit fancier
*
* @return void
**/
function isEditor()
{
    if (php_uname('n') === 'phoebe' /*&& PHP_SAPI == 'cli'*/) {
        // for now only allow editing on phoebe
        return true;
    }
    
    return false;
}



