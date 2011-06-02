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
* Sidebar
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Sidebar
{
    /**
    * Load github user json, and return project list
    *
    * @param string $username USername on github to pull
    *
    * @return html
    **/
    public static function github($username = false)
    {
        if ( !$username ) {
            return json("Nu User Specified");
        }
        
        $cache_file = option('cache_dir') . "/github-{$username}.json";
        
        if ( file_exists($cache_file) ) {
            $stat = stat($cache_file);
            
            if ($stat['mtime'] < time() + 60*60*24) {
                return json(file_get_contents($cache_file));
            }
        }
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, "http://github.com/api/v1/json/" . $username);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 

        $ret = curl_exec($ch);
        curl_close($ch);
        
        if (json_decode($ret) !== false) {
            file_put_contents($cache_file, $ret);
        }

        return json($ret);
    }

}

