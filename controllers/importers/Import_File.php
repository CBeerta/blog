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

if ( PHP_SAPI != 'cli' ) {
    // dont do anything if we're not a cli php
    return;
}

/**
* Wordpress Blog Post Importer
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Import_File
{
    /**
    * Cling App
    **/
    private $_cling;
    
    /**
    * Constructor
    *
    * @param object $cling Cling Application
    *
    * @return object $post modified post 
    **/
    public function __construct($cling)
    {
        $this->_cling = $cling;
    }

    /**
    * Import Files for Posts
    *
    * @return void
    **/
    public function run()
    {
        $posts_dir = $this->_cling->option('posts_dir');
        $created = 0;
        
        $glob = "{{$posts_dir}/*.html,{$posts_dir}/*.mkd}";
        foreach (glob($glob, GLOB_BRACE) as $filename) {
            preg_match('#/(\d+-\d+-\d+) (.*)\.(\w+)$#', $filename, $matches);

            if (count($matches) != 4) {
                // FIXME: should output a warning of some sort
                continue;
            }
            
            echo "Importing: {$filename}";

            $content = file($filename);
            
            $post_date = strtotime($matches[1]);
            $title = $matches[2];
            $tags = array();
            
            foreach ($content as $k => $line) {
            
                if (preg_match("#^Title:\s?(.*)$#i", $line, $matches)) {
                    $title = $matches[1];
                    unset($content[$k]);
                } else if (preg_match("#^Tags:\s?(.*)$#i", $line, $matches)) {
                    $tags = explode(',', $matches[1]);
                    unset($content[$k]);
                }
            
            }
            
            $post = ORM::for_table('posts')
                ->where('post_title', $title)
                ->find_one();
                
            if (!$post) {
                print "... Creating: ";
                $post = ORM::for_table('posts')->create();
                $post->post_status = 'publish';
                $created++;
            } else {
                if ($post->protected != 0) {
                    print "... Post is Protected, skipping\n";
                    continue;
                }
                print "... Updating: ";
            }
            
            print $title;
            
            $post->post_date = date('c', $post_date);
            $post->post_slug = Helpers::buildSlug($title);
            $post->post_title = $title;
            $post->protected = 0;
            $post->post_content = implode("", $content);
            $post->guid = $post->post_slug . '-' . time();
            $post->original_source = $filename;
            
            if (!$this->_cling->option('dry-run')) {
                $post->save();
                Helpers::addTags($tags, $post->ID);
                print "... done\n";
            } else {
                print "... Dry Run, not saving\n";
            }
        }
        
        return $created;
    }
}

