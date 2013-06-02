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
class Import_Google
{
    /**
    * Google ID
    **/
    private $_google_id;

    /**
    * Google Api Key
    **/
    private $_google_api_key;

    /**
    * Dry Run
    **/
    private $_dry_run = true;
        
    /**
    * Cling App
    **/
    private $_cling;
    
    /**
    * Constructor
    *
    * @return object $post modified post 
    **/
    public function __construct()
    {
    }

    /**
    * Set Cling
    *
    * @param object $cling Cling Application
    *
    * @return void
    **/
    public function setCling($cling)
    {
        $this->_cling = $cling;
    }
    
    /**
    * Create a new blog post by Email
    *
    * @return void
    **/
    public function run()
    {
        $created = 0;
        $page_token = null;
        
        $this->_google_id = $this->_cling->option('google_id');
        $this->_google_api_key = $this->_cling->option('google_api_key');
        $this->_dry_run = $this->_cling->option('dry-run');

        do {

            $url = 'https://www.googleapis.com/plus/v1/people/';
            $url .= $this->_google_id;
            $url .= '/activities/public?alt=json&pp=1&key=';
            $url .= $this->_google_api_key;
            $url .= '&pageToken=';
            $url .= $page_token;
            
            $page_token = null;
            
            // d("## Loading: {$url}.");
            $res = file_get_contents($url);
            $json = json_decode($res);
            
            if (!$json) {
                d("Unable to parse json. Aborting.");
                break;
            }
            
            if (isset($json->nextPageToken)) {
                $page_token = $json->nextPageToken;
            }
            
            if (!isset($json->items)) {
                d("No More Items. Aborting.");
                break;
            }
            
            foreach ($json->items as $item) {
                $ret = $this->handleItem($item);
                
                if ($ret === false || !is_numeric($ret)) {
                    continue;
                }
                
                // Import Comments for existing posts.
                $this->importComments($ret, $item->object->replies);
                
                // Import Tags from post
                $tags = array('Google+');
                preg_match_all(
                    '|#(?P<tag>[a-z]+)|i', 
                    $item->object->content, 
                    $matches
                );
                if (!empty($matches['tag'])) {
                    $tags = array_merge($tags, $matches['tag']);
                }

                Helpers::addTags($tags, $ret);
            }
        
        } while ($page_token !== null);
        
        return $created;
    }

    /**
    * Handle an Item from G+
    *
    * @param object $item The items 'object'
    *
    * @return int
    **/
    public function handleItem($item)
    {
        $content = $item->object->content;
        $pos = strpos($content, '<br />');
        $title = strip_tags(substr($content, 0, $pos));

        // Handle Attachments
        $content = $this->handleAttachments($content, $item);

        if (empty($title)) {
            d("Invalid Post: '{$item->object->content}'.");
            return false;
            $title = $content;
        } else {
            // Strip title and one '<br />' from content
            $content = substr($content, $pos + 6);
        }

        $post_slug = Helpers::buildSlug("gplus {$title}");
        
        $post = ORM::for_table('posts')
            ->where('post_title', $title)
            ->where_not_like('original_source', '%googleapis%')
            ->find_one();
            
        if (!$post) {
            d("Creating: {$title}. Type: {$item->object->objectType}.");
            $post = ORM::for_table('posts')->create();
            $post->post_status = 'publish';
        } else {
            d("Updating: {$title}. Type: {$item->object->objectType}.");

            // If a post is from somewhere but google+, don't update it. 
            // This is usually stuff pulled via picasa, then shared on G+
            // Return the ID though, so comments can be pulled
            if ($post->post_type != 'blog') {
                return $post->ID;
            }
        }

        $parsed_date = strtotime($item->published);
        if ($parsed_date === false) {
            d("Can't Parse Date: {$item->published}.");
            return false;
        }
        
        $post->post_date = date('c', $parsed_date);
        $post->post_slug = $post_slug;
        $post->post_title = $title;
        $post->post_content = $content;
        $post->guid = $post->post_slug . '-' . time();
        $post->original_source = $item->url;
        $post->post_type = 'blog';

        if (!$this->_dry_run) {
            $post->save();
            return $post->ID;
        }        

        return false;
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

            // This is probably a 'Note' that has a Photo attached
            // Look for matching Post, so we can maybe attach a comment to it
            if (isset($attachment->displayName)) {
                $post = ORM::for_table('posts')
                    ->where('post_title', $attachment->displayName)
                    ->find_one();
            }
                            
            switch ($attachment->objectType) {
            case 'photo':

                //FIXME: Fabricate the Content here?
                
                //$content .= '<a href="' . $attachment->url;
                //$content .= '"><img src="' . $attachment->image->url;
                //$content .= '"></a>';
                
                if (isset($post->ID) && isset($attachment->displayName)) {
                    // There is a Matching Post, thus try to import 
                    // The Comments associated with it.
                    d("Importing Comments on '{$attachment->displayName}'.");
                    $this->importComments($post->ID, $item->object->replies);
                }
                break;
                
            case 'article':
                $this->_cling->view->set('attachment', $attachment);
                $content .= $this->_cling->view->fetch(
                    'snippets/importer.google.article.html.php'
                );
                break;

            case 'photo-album':
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
        $url = $replies->selfLink . '?key=' . $this->_google_api_key;

        //d("### Comments: {$url}");
        
        $res = file_get_contents($url);
        $json = json_decode($res);

        if (!$json) {
            d("Unable to parse json. Aborting.");
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
                d("\t- New Comment from '{$item->actor->displayName}', Importing.");
                $comment = ORM::for_table('comments')->create();
                $comment->post_ID = $ID;
                $comment->comment_status = 'visible';
            } else {
                d("\t- Updating Comment from '{$item->actor->displayName}'.");
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
            
            if (!$this->_dry_run) {
                $comment->save();
            }
        }

    }
    
}




