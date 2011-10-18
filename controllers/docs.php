<?php 
/**
* Homebrew Website of Claus Beerta
*
* PHP Version 5.3
*
* Copyright (C) 2011 by Claus Beerta
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
* Docs
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Docs
{
    /**
    * loadDocs stores the most up-to-date doc_name here
    **/
    private static $_latest = null;
    
    /**
    * Load all Documents and store info aswell as find latest
    *
    * @return array
    **/
    public static function loadDocs()
    {
        $docs = array();
        $file_timestamps = array();
 
        $docs_dir = Slim::config('docs_dir');
        
        $glob = "{{$docs_dir}/*.html,{$docs_dir}/*.mkd}";
        foreach (glob($glob, GLOB_BRACE) as $filename) {
        
            // Don't want unreadable files
            if (!is_readable($filename)) {
                continue;
            }

            $basename = basename($filename);
            $topics = array();

            preg_match('#(.*?)\.(\w+)$#', $basename, $matches);
            $doc_name = $matches[1];
            
            // load file
            $data = file($filename);

            // load topics from the file
            foreach ($data as $line) {
                if (!preg_match('|^#(\s)?(.*)$|', $line, $matches)) {
                    continue;
                }
                $topics[] = $matches[2];
            }
            unset($data);

            // store timestamps
            $stat = stat($filename);
            $file_timestamps[$stat['mtime']] = $doc_name;

            $docs[$doc_name] = (object) array(
                'filename' => $filename,
                'doc_name' => $doc_name,
                'topics' => $topics,
                'stat' => $stat,
                'content' => 'to-be-loaded',
            );
            
        }
        
        // Store the most recent document title
        ksort($file_timestamps);
        self::$_latest = array_pop($file_timestamps);
        
        return $docs;
    }
    
    /**
    * Load the actual Content for this document
    *
    * @param object $doc Doc to load content for
    *
    * @return object
    **/
    public static function loadContent($doc)
    {
        $data = file_get_contents($doc->filename);
        $doc->content = Markdown($data);
        
        return $doc;
    }
    
    /**
    * Documentation or 'Brain Dump' Index page
    *
    * @param string $slug filename of file to show
    *
    * @return html
    **/
    public static function index($slug = null)
    {
        $docs = self::loadDocs();
        
        if ($slug === null) {
            $content = $docs[self::$_latest];
        } else {
            $content = $docs[$slug];
        }
        
        Slim::view()->appendData(
            array(
                'docs' => $docs, 
                'display' => self::loadContent($content),
            )
        );
        
        return Slim::render('docs.html');
    }

}



