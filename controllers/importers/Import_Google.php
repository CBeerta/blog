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

                // Strip title and one '<br />' from content
                $content = substr($content, $pos + 6);
                
                // Handle Attachments
                $content = self::handleAttachments($content, $item);
                
                $post = ORM::for_table('posts')
                    ->where('post_title', $title)
                    ->find_one();
                    
                if (!$post) {
                    print "## Creating: {$title}\n";
                    $post = ORM::for_table('posts')->create();
                    $post->post_status = 'publish';
                } else {
                    
                    // Import Comments for existing posts.
                    if (isset($item->object->replies)) {
                        self::importComments($post->ID, $item->object->replies);
                    }
                    
                    // If a post is from somewhere but google+,
                    // don't update it. 
                    // This is usually stuff pulled via picasa, then shared on G+
                    if ($post->post_type != 'blog') {
                        print "Skipping: {$title}.\n";
                        continue;
                    }
                    
                }

                $parsed_date = strtotime($item->published);
                if ($parsed_date === false) {
                    d("Can't Parse Date: {$item->published}.");
                    continue;
                }
                
                $post->post_date = date('c', $parsed_date);
                $post->post_slug = Helpers::buildSlug($title);
                $post->post_title = $title;
                $post->post_content = $content;
                $post->guid = $post->post_slug . '-' . time();
                $post->original_source = $item->url;
                $post->post_type = 'blog';

                if (!$dryrun) {
                    $post->save();
                    // FIXME: Should parse '#' tags in posts and add them aswell
                    Helpers::addTags(array('Google+'), $post->ID);
                } else {
                    print "Dry Run, not saving\n";
                }
            }
        
        } while ($page_token !== null);
 
    }

    /**
    * Handle Attachments
    * FIXME: This needs more work to handle all possible attachments
    *
    * @param string $content String with current content
    * @param object $item    The items 'object'
    *
    * @return string
    **/
    public function handleAttachments($content, $item)
    {
        if (!isset($item->object->attachments)) {
            return $content;
        }
    
        foreach ($item->object->attachments as $attachment) {
            switch ($attachment->objectType) {
            case 'photo':
                $content .= '<a href="' . $attachment->url;
                $content .= '"><img src="' . $attachment->image->url;
                $content .= '"></a>';
                break;
            default:
                break;
            }
        }
        
        return $content;
    }

    /**
    * Import Comments to a G+ Post
    *
    * @param int    $ID      postID to add comments to
    * @param object $replies Object with the Url to the comments
    *
    * @return bool
    **/
    public function importComments($ID, $replies)
    {
        $dryrun = $this->dryrun;
        $google_id = Slim::config('google_id');
        $api_key = Slim::config('google_api_key');

        $url = $replies->selfLink . '?key=' . $api_key;

        print "### Comments: {$url}\n";
        
        $res = file_get_contents($url);
        $json = json_decode($res);
        
        if (!$json) {
            print "Unable to parse json. Aborting.\n";
            return false;
        }
        
        if (!isset($json->items)) {
            return false;
        }
       
        foreach ($json->items as $item) {

            $comment = ORM::for_table('comments')
                ->where('original_source', $item->selfLink)
                ->find_one();
                
            if (!$comment) {
                $comment = ORM::for_table('comments')->create();
                $comment->post_ID = $ID;
                $comment->comment_status = 'visible';
            } else {
                print "Comment from '{$item->actor->displayName}' Already Exists.\n";
            }

            $parsed_date = strtotime($item->published);
            if ($parsed_date === false) {
                d("Can't Parse Date: {$item->published}.");
                continue;
            }
            
            $comment->comment_author = $item->actor->displayName;
            $comment->comment_author_url = $item->actor->url;
            $comment->comment_author_email = $item->actor->image->url;
            $comment->comment_date = date('c', $parsed_date);
            $comment->comment_content = $item->object->content;
            $comment->original_source = $item->selfLink;

            $comment->save();
        }

    }
    
}




