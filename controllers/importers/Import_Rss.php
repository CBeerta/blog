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
            // FIXME this might be somewhat DeviantART Specific
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
        
        $post->post_type = 'deviantart';
        $post->post_content = $content;

        return $post;
    }
    
    /**
    * Resize an image, keep aspect ratio
    *
    * @param img  $src           Source Image (GD)
    * @param int  $target_width  How wide should the image be
    * @param int  $target_height And how Hight
    * @param bool $force_size    Force Target widh, or use calculated size
    *
    * @return img $dest New resized Image
    **/
    private function _imgResize($src, $target_width, $target_height, $force_size)
    {
        $width = imagesx($src);
        $height = imagesy($src);

        $imgratio = ($width / $height);

        if ($imgratio>1) { 
            $new_width = $target_width; 
            $new_height = ($target_width / $imgratio); 
        } else { 
            $new_height = $target_height; 
            $new_width = ($target_height * $imgratio); 
        }
        
        if ($force_size) {
            // Force new image to be of target size
            $dest = imagecreatetruecolor($target_width, $target_height);
        } else {
            // will use aspect ratio
            $dest = imagecreatetruecolor($new_width, $new_height);
        }

        imagecopyresampled(
            $dest, 
            $src, 
            0, 
            0, 
            0,
            $force_size ? ($height/2) : 0,
            $new_width,
            $new_height,
            $width,
            $height
        );
        
        return $dest;
    }
    
    /**
    * Manipulate content for flickr pull
    *
    * @param object $item simplepies item
    * @param object $post an idiorm object with the post
    *
    * @return object $post modified post 
    **/
    private function _flickr($item, $post)
    {
        if (!function_exists("imagecreatefromjpeg")) {
            d("Requires GD to be installed");
            die;
        }
        $orig_img = $item->get_enclosure()->link;
        
        $dest_thumb_file = Slim::config('public_loc') . 
            'flickrthumb_' . 
            basename($orig_img);
        $dest_file = Slim::config('public_loc') . basename($orig_img);

        if (file_exists($dest_file) && $this->force === false ) {
            d("Not regeneration thumb");
        } else {
            d("Loading image: " . $orig_img);
            
            $src = imagecreatefromjpeg($orig_img);
            
            imagejpeg($this->_imgResize($src, 1900, 1200, false), $dest_file);

            $thumb = $this->_imgResize($src, 940, 255, true);

            $bg = imagecolorallocatealpha($thumb, 0, 0, 0, 40);
            $white = imagecolorallocatealpha($thumb, 255, 255, 255, 10);
            imagefilledrectangle($thumb, 0, 230, 940, 255, $bg);
            $font = '/usr/share/fonts/truetype/ttf-bitstream-vera/VeraSe.ttf';
            imagettftext($thumb, 13, 0, 10, 248, $white, $font, $item->get_title());
                        
            imagejpeg($thumb, $dest_thumb_file);
        }
        
        $content  = '<a href="';
        $content .= Slim::config('public_url') . basename($dest_file);
        $content .= '" title="' . $post->post_title . '">';
        $content .= '<img src="';
        $content .= Slim::config('public_url') . basename($dest_thumb_file);
        $content .= '" width="940" height="255"></a>';

        $post->post_type = 'flickr';
        $post->post_content  = $content;

        return $post;
    }
    
    /**
    * Import RSS Feeds into blog
    *
    * @return void
    **/
    public function run()
    {
        $feed_uri = $this->value;
        $dryrun = $this->dryrun;
        $force = $this->force;
    
        $parsed_url = parse_url($feed_uri);
        
        d("Will import {$feed_uri}");
        
        $rss = new SimplePie();        
        
        $rss->set_feed_url($feed_uri);
        $rss->set_cache_location('/var/tmp');
        $rss->set_cache_duration(43200);
        $rss->init();
        $rss->handle_content_type();
        
        // don't sort by pubdate, 
        $rss->enable_order_by_date(false); 
        
        $items = array();
        foreach ( $rss->get_items() as $item ) {
            $post = ORM::for_table('posts')
                ->where_like('post_title', $item->get_title())
                ->order_by_desc('post_date')
                ->find_one();

            if (isset($post->ID) && $force === false) {
                d("Skipping: " . $post->post_title);
                continue;
            }
            
            d("Importing: " . $item->get_title());
            
            if (isset($post->ID)) {
                $new = ORM::for_table('posts')->find_one($post->ID);
            } else {
                $new = ORM::for_table('posts')->create();
            }

            /**
            * Basic style if there is no custom one
            **/
            $new->post_status = 'draft';
            $new->post_title = $item->get_title();
            $new->post_date = $item->get_date('c');
            $new->post_slug = Helpers::buildSlug($item->get_title()) . '-' 
                . basename(strtolower($item->get_id()));
            $new->guid = $new->post_slug;
            $new->original_source = $item->get_link();
            $new->post_content = $item->get_content();

            switch ($parsed_url['host'])
            {
            case 'api.flickr.com':
                $new = $this->_flickr($item, $new);
                break;
            case 'backend.deviantart.com':
                $new = $this->_deviantArt($item, $new);
                break;
            }

            if (!$dryrun) {
                $new->save();
            }
            
            d($new->as_array());
        }
    }

}
