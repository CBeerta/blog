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
class Import_Rss extends Importer
{
    /**
    * DevArt Specific content
    *
    * @param object $item simplepies item
    * @param object $post an idiorm object with the post
    *
    * @return object $post modified post
    *
    * FIXME: Gee, HTML pastery action. I should shoot myself.
    **/
    private function _deviantArt($item, $post)
    {
        $content  = '<div class="rss-imported">';
        
        if (isset($item->get_enclosure()->thumbnails[0])) {
            $content .= '<a href="' . $item->get_enclosure()->link . '">';
            $content .= '<img src="' . $item->get_enclosure()->thumbnails[0];
            $content .= '"></a>';
        }
        
        if (isset($item->get_enclosure()->description)) {
            $content .= $item->get_enclosure()->description;
        } else {
            $content .= $item->get_content();
        }
        
        $content .= '</div><br/>';
        
        $post->post_content = $content;

        return $post;
    }
    
    /**
    * Manipulate content for image pull
    *
    * @param object $item simplepies item
    * @param object $post an idiorm object with the post
    *
    * @return object $post modified post 
    **/
    private function _photography($item, $post)
    {
        if (!function_exists("imagecreatefromjpeg")) {
            d("Requires GD to be installed");
            die;
        }
        
        $orig_img = $item->get_enclosure()->link;
        
        $dst_name = str_replace('%', '_', basename($orig_img));
        
        $dest_thumb_file = Helpers::option('public_loc') . 
            'thumb_' . 
            $dst_name;

        $square_thumb_file = Helpers::option('public_loc') . 
            'wallpaper_thumb_' . 
            $dst_name;

        $dest_file = Helpers::option('public_loc') . basename($dst_name);

        if (file_exists($dest_file) && file_exists($dest_thumb_file) ) {
            d("Not regenerating thumb {$dst_name}");
            $img = new Resize($dest_file);
        } else {
            d("Loading image: " . $orig_img);
            
            $img = new Resize($orig_img);
            $img->resizeImage(1920, 1200);
            $img->saveImage($dest_file);

            $img->resizeImage(250, 150, 'crop');
            $img->saveImage($square_thumb_file);
            
            $img->resizeImage(580, 385, 'crop');
            $img->addText($item->get_title());
            $img->saveImage($dest_thumb_file);
        }
        $dimensions = $img->dimensions();
        unset($img);
        
        $post->guid = $dst_name;

        $post->post_meta = array(
            'photo_width' => $dimensions['width'],
            'photo_height' => $dimensions['height'],
            'filename' => $dst_name,
            );

        $content  = '<a href="';
        $content .= Helpers::option('public_url') . basename($dest_file);
        $content .= '" title="' . $post->post_title . '">';
        $content .= '<img src="';
        $content .= Helpers::option('public_url') . basename($dest_thumb_file);
        $content .= '" width="580" height="385"></a>';

        if (preg_match(
            '#.*>Date:\s+(.*?(AM|PM))<.*#i', 
            $item->get_content(), 
            $matches
        )) {
            /* Picasa's post_date is CRAP, parse it from the content */
            $parsed_date = strtotime($matches[1]);

            if ($parsed_date !== false) {
                $post->post_date = date('c', $parsed_date);
            }
        }

        $post->post_content = $content;

        return $post;
    }
    
    /**
    * Import RSS Feeds into blog
    *
    * @return void
    **/
    public function run()
    {
        $created = 0;
        $parsed_url = parse_url($this->value);
        
        d("Will import {$this->value}");
        
        $rss = new SimplePie();        
        
        $rss->set_feed_url($this->value);
        $rss->set_cache_location('/var/tmp');
        $rss->set_cache_duration(60);
        $rss->init();
        $rss->handle_content_type();
        
        // don't sort by pubdate, 
        $rss->enable_order_by_date(false); 

        $post_type = !is_null($this->post_type) 
            ? $this->post_type 
            : 'blog';
        
        $items = array();
        foreach ($rss->get_items() as $item) {

            $post_slug =  $post_type . '-' . Helpers::buildSlug($item->get_title());
            
            if ($post_type == 'activity') {
                $post_slug .= '-' . md5($item->get_id());
            }
            
            $post = ORM::for_table('posts')
                ->where_like('post_slug', $post_slug)
                ->order_by_desc('post_date')
                ->find_one();
            
            if (isset($post->ID)) {
                d("Updating: " . $post->post_slug);
            } else {
                d("Adding: " . $post_slug);
                $post = ORM::for_table('posts')->create();
                $post->post_date = $item->get_date('c');
                $post->post_status = 'publish';
                $post->post_title = $item->get_title();
                $created++;
            }

            /**
            * Basic style if there is no custom one
            **/

            $post->post_type = $post_type;
            $post->post_slug = $post_slug;
            $post->guid = Helpers::buildSlug($item->get_title()) . '-' 
                . md5($item->get_id());
            $post->original_source = $item->get_link();
            $post->post_content = $item->get_content();
            
            $tags = array('Imported');

            switch ($parsed_url['host'])
            {
            case 'api.flickr.com':
                $post = $this->_photography($item, $post);
                $tags = array('Photo', 'Flickr');
                break;
            case 'picasaweb.google.com':
                $post = $this->_photography($item, $post);
                $tags = array('Photo', 'Picasa', $rss->get_title());
                break;
            case 'backend.deviantart.com':
                $post = $this->_deviantArt($item, $post);
                $tags = array('deviantArt');
                break;
            case 'github.com':
                $tags = array('Github');
                break;
            }
            
            $post_meta = $post->post_meta;
            unset($post->post_meta);
            
            //d($post->as_array());
            
            if (!$this->dryrun) {
                $post->save();
                Helpers::addTags($tags, $post->ID);
            }
            
            if (!empty($post_meta) && !$this->dryrun) {
                ORM::for_table('post_meta')
                    ->where_equal('posts_ID', $post->ID)
                    ->delete_many();
                    
                foreach ($post_meta as $k => $v) {
                    $meta = ORM::for_table('post_meta')->create();
                    $meta->posts_ID = $post->ID;
                    $meta->meta_key = $k;
                    $meta->meta_value = $v;
                    
                    $meta->save();
                }
            }
            
        }
        
        return $created;
    }

}
