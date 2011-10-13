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
class Import_Google extends Importer
{
    /**
    * Create a new blog post by Email
    *
    * @return void
    **/
    public function run()
    {
        $dryrun = $this->dryrun;
        $page_token = null;
        
        $google_id = Slim::config('google_id');
        $api_key = Slim::config('google_api_key');

        do {

            $url = 'https://www.googleapis.com/plus/v1/people/';
            $url .= $google_id;
            $url .= '/activities/public?alt=json&pp=1&key=';
            $url .= $api_key;
            $url .= '&pageToken=';
            $url .= $page_token;
            
            $page_token = null;
            
            print "## Loading: {$url}\n";
            $res = file_get_contents($url);
            $json = json_decode($res);
            
            if (!$json) {
                print "Unable to parse json. Aborting.\n";
                break;
            }
            
            if (isset($json->nextPageToken)) {
                $page_token = $json->nextPageToken;
            }
            
            if (!isset($json->items)) {
                print "No More Items. Aborting.\n";
                break;
            }
            
            foreach ($json->items as $item) {
            
                $content = $item->object->content;
                $pos = strpos($content, '<br />');
                $title = strip_tags(substr($content, 0, $pos));

                if (empty($title)) {
                    print "Invalid Post: {$content}\n";
                    continue;
                }

                # Strip title and one '<br />' from content
                $content = substr($content, $pos + 6);
                
                foreach ($item->object->attachments as $attachment) {
                    print_r($attachment);
                    switch ($attachment->objectType) {
                    case 'photo':
                        break;
                    default:
                        break;
                    }
                }
                
                $post = ORM::for_table('posts')
                    ->where('post_title', $title)
                    ->find_one();
                    
                if (!$post) {
                    print "## Creating: {$title}\n";
                    $post = ORM::for_table('posts')->create();
                    $post->post_status = 'draft';
                } else {
                    if (isset($item->object->replies)) {
                        self::importComments($post->ID, $item->object->replies);
                    }
                    print "## Post '{$title}' already exists, skipping\n";
                    continue;
                }

                $parsed_date = strtotime($item->published);
                if ($parsed_date === false) {
                    d("Can't Parse Date: {$item->published}.");
                    continue;
                }
                
                $post->post_date = $parsed_date;
                $post->post_slug = Helpers::buildSlug($title);
                $post->post_title = $title;
                $post->post_content = $title;
                $post->guid = $post->post_slug . '-' . time();
                $post->original_source = $item->url;
                
                if (!$dryrun) {
                    $post->save();
                    //Helpers::addTags($tags, $post->ID);
                } else {
                    print "Dry Run, not saving\n";
                }
                //self::importComments($post->ID, $item->
                //print_r($post);
            }
        
        } while ($page_token !== null);
 
    }

    /**
    * Import Comments to a G+ Post
    *
    * @param int    $ID      postID to add comments to
    * @param object $replies Object with the Url to the comments
    *
    * @return void
    **/
    public function importComments($ID, $replies)
    {
        $dryrun = $this->dryrun;
        $google_id = Slim::config('google_id');
        $api_key = Slim::config('google_api_key');

        $url = $replies->selfLink . '?key=' . $api_key;

        print "### Comments: {$url}\n";

    }
    
}




