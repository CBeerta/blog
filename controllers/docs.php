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
    * Documentation or 'Brain Dump' Index page
    *
    * @param string $slug filename of file to show
    *
    * @return html
    *
    * @FIXME: this will get slow with lots of files. fortunetly i'll never ever
    *         write nearly enough docs for this to ever bevome a problem. EVER
    **/
    public static function index($slug = null)
    {
        $topics = array();
        $sub_topics = array();
        $file_timestamps = array();
 
        $docs_dir = Slim::config('docs_dir');
        
        $glob = "{{$docs_dir}/*.html,{$docs_dir}/*.mkd}";
        foreach (glob($glob, GLOB_BRACE) as $filename) {

            // Don't want unreadable files
            if (!is_readable($filename)) {
                continue;
            }

            preg_match('#.*/(.*?)\.(\w+)$#', $filename, $matches);
            $topic = $matches[1];
            $topics[] = $topic;

            // load file
            $data = file($filename);

            // if this is the one, then keep it
            if ($slug == $topic) {
                $content = Markdown(implode("", $data));
            }
            
            // load topics from the file
            foreach ($data as $line) {
                if (!preg_match('|^#(\s)?(.*)$|', $line, $matches)) {
                    continue;
                }
                $sub_topics[$topic][] = $matches[2];
            }
            
            // 
            $stat = stat($filename);
            $file_timestamps[$stat['mtime']] = $filename;
        }        
        
        // No slug selected, so pick whatever was edited most recently        
        if ($slug === null) {
            ksort($file_timestamps);
            $filename = array_pop($file_timestamps);
            $content = Markdown(file_get_contents($filename));
        }
        
        Slim::view()->appendData(
            array(
                'topics' => $topics,
                'sub_topics' => $sub_topics,
                'content' => $content,
            )
        );
        
        return Slim::render('docs.html');
    }


}



