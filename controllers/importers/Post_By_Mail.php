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
class Post_By_Mail extends Importer
{
    /**
    * Create a new blog post by Email
    *
    * @return void
    **/
    public function run()
    {
        $value = $this->value;
        $dryrun = $this->dryrun;
        $force = $this->force;
        
        if (ftell(STDIN) !== 0) {
            fwrite(STDERR, "Nothing on STDIN.\n");
            return;
        }
        
        $content = '';
        $input = fopen('php://stdin', 'r');
        
        $headers = array();
        $body_opened = false;
        
        while ($line = fgets($input)) {
            
            if (preg_match("#^(\S+):\s?(.*)\Z#i", $line, $matches)) {
                // Pull the Headers
                $headers[strtolower($matches[1])] = $matches[2];
            } else if (preg_match("#^\Z#", $line) && count($headers) > 0) {
                // Newline without anything, and some headers seen
                // this must be the body
                $body_opened = true;
            } else if ($body_opened && preg_match("#^--\s\Z#", $line)) {
                // --\s on a single line marks the signature. close body
                $body_opened = false;
            } else if ($body_opened) {
                $content .= $line;
            } else {
                // ignore anything else that is not header or body
            }

        }
        
        if (!isset($headers['subject']) || empty($content)) {
            d("Can't deal with this mail");
            return;
        }

        if ($headers['delivered-to'] !== 'claus@aello.beerta.net'
            || $headers['return-path'] !== '<claus@beerta.net>'
        ) {
            d("Source not verified, not posting");
            return;
        }
        
        $post = ORM::for_table('posts')
            ->where('post_title', $headers['subject'])
            ->find_one();
            
        if (!$post) {
            $post = ORM::for_table('posts')->create();
        }
        
        $post->post_date = date('c');
        $post->post_slug = Helpers::buildSlug($headers['subject']);
        $post->post_title = $headers['subject'];
        $post->post_content = trim($content);
        $post->guid = $post->post_slug . '-' . mktime();
        $post->post_status = 'draft';
        
        if (!$dryrun) {
            $post->save();
        }
        
        d($headers);

        //d($post);
    }
}

