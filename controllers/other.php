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
* Sidebar
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Other
{
    /**
    * Return a Sitemap
    *
    * @return xml
    **/
    public static function sitemap()
    {
        $posts = ORM::for_table('posts')
            ->where('post_status', 'publish')
            ->order_by_desc('post_date')
            ->find_many();

        $posts = Projects::mergeBlogPosts($posts);
        reset($posts);
        
        Slim::view()->appendData(
            array(
            'lastmod' => current($posts)->post_date->format('Y-m-d'),
            'posts' => $posts,
            )
        );
        
        Slim::response()->header('Content-Type', 'application/xml');        
        return Slim::render('sitemap.xml');
    }

    /**
    * Load github user json, and return project list
    *
    * @return json
    **/
    public static function search()
    {
        $s = isset($_POST['s']) ? sqlite_escape_string($_POST['s']) : false;
        
        $posts = ORM::for_table('posts')
            ->select_expr(Blog::_POSTS_SELECT_EXPR)
            ->where_raw(
                "
                (
                `post_content` LIKE '%{$s}%' OR 
                `post_title` LIKE '%{$s}%'
                )
                AND
                `post_status` = 'publish'
                "
            )
            ->order_by_desc('post_date')
            ->limit(10)
            ->find_many();

        Slim::view()->appendData(
            array(
            'title' => 'Search',
            'active' => 'blog',
            'posts' => $posts,
            'search' => $s,
            )
        );
        return Slim::render('blog/search.html');
    }
    /**
    * Load github user json, and return project list
    *
    * @param string $username Username on github to pull
    *
    * @return json
    **/
    public static function github($username = false)
    {
        if ( !$username ) {
            return json("No User Specified");
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        $ret = curl_exec($ch);
        curl_close($ch);
        
        if (json_decode($ret) !== false) {
            file_put_contents($cache_file, $ret);
        }

        return json($ret);
    }


    /**
    * Load Devbiantart RSS and extract images
    *
    * @param string $q The query to search for on devArt
    *
    * @return json
    **/
    public static function deviantart($q = false)
    {
        include_once __DIR__.'/vendor/simplepie/SimplePieAutoloader.php';
        
        if ( !$q ) {
            return json("No search specified");
        }

        $rss = new SimplePie();        
        
        $rss->set_feed_url(
            "http://backend.deviantart.com/rss.xml?q={$q}" . 
            "&type=deviation" . 
            "&offset=0"
        );
        $rss->set_cache_location('/var/tmp');
        $rss->set_cache_duration(43200);
        $rss->init();
        $rss->handle_content_type();
        
        // don't sort by pubdate, 
        // but rather the date i added it to my favs
        $rss->enable_order_by_date(false); 
        
        
        $items = array();
        foreach ( $rss->get_items(0, option('deviantart_items')) as $item ) {
            if ($enclosure = $item->get_enclosure()) {

                list ($big, $small) = $enclosure->get_thumbnails();
                list ($author, $author_img) = $enclosure->get_credits();
                
                $items[] = array(
                    'link' => $item->get_link(),
                    'title' => $item->get_title(),
                    'small' => $small,
                    'big' => $big,
                    'author' => $author->get_name(),
                    'author_img' => $author_img->get_name(),
                );
            }
        }
        return json(json_encode($items));
    }
    
}

