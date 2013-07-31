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

use \Slim\Slim;

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
        $app = Slim::getInstance();
        
        $posts = ORM::for_table('posts')
            ->where('post_status', 'publish')
            ->order_by_desc('post_date');
        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();

        $posts = Projects::mergeBlogPosts($posts);

        reset($posts);

        if (count($posts) < 1) {
            $app->response()->status(404);
            return $app->render('404.html');
        }
        
        $app->view()->appendData(
            array(
            'lastmod' => current($posts)->post_date->format('Y-m-d'),
            'posts' => $posts,
            )
        );
        
        $app->response()->header('Content-Type', 'application/xml');        
        return $app->render('sitemap.xml');
    }

    /**
    * Search for something
    *
    * @return json
    **/
    public static function search()
    {
        $app = Slim::getInstance();
        $ppp = Helpers::option('posts_per_page');
        
        $s = isset($_POST['s']) ? SQLite3::escapeString($_POST['s']) : false;
        
        $posts = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->where_raw(
                "
                (
                `post_content` LIKE '%{$s}%' OR 
                `post_title` LIKE '%{$s}%'
                )
                "
            )
            ->order_by_desc('post_date')
            ->limit($ppp);
            
        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();

        $app->view()->appendData(
            array(
            'active' => 'blog',
            'posts' => $posts,
            'search' => $s,
            'base_url' => '/sidebar/search',
            )
        );

        return $app->render('blog/index.html');
    }

    /**
    * not Found Magic
    *
    * @return void
    **/
    public static function notFound()
    {
        $app = Slim::getInstance();
        
        $parts = explode('/', $app->request()->getResourceUri());
        
        /**
        * First look through all projects and see if theres a match
        **/
        $slugs = array();
        $projects = Projects::loadProjects();
        
        foreach ($projects as $proj) {
            $slugs[strtolower($proj->post_slug)] = strtolower($proj->post_slug);
        }
        
        foreach ($parts as $part) {
            if (in_array(strtolower($part), $slugs)) {
                $app->redirect('/projects/' . $slugs[strtolower($part)]);
            }
        }
        
        /**
        * Then look at all blog posts for a match
        **/
        $posts = ORM::for_table('posts')->find_many();
        
        $slugs = array();
        foreach ($posts as $post) {
            $slugs[] = $post->post_slug;
        }

        foreach ($parts as $part) {
            if (in_array($part, $slugs)) {
                $app->redirect('/blog/' . $part);
            }
        }
        
        /**
        * Finally, render our 404 because nothing was found
        **/
        $app->render('404.html');
    }


    /**
    * Build a Tag Cloud
    *
    * @return array
    **/
    public static function tagCloud()
    {
        $smallest = 8;
        $largest = 28;
        
        $tags = ORM::for_table('post_terms')
            ->select_expr(
                '
                *,
                (
                    SELECT COUNT(ID) FROM `posts`, `term_relations`
                    WHERE 
                        term_relations.post_terms_ID=post_terms.ID AND
                        term_relations.posts_ID=posts.ID AND
                        posts.post_status="publish"
                ) AS posts_with_tag
                '
            )
            ->order_by_asc('slug')
            ->find_many();

        if (count($tags) < 1) {
            return false;
        }

        $counts = array();
        $real_counts = array(); // For the alt tag
        foreach ( (array) $tags as $key => $tag ) {
            $real_counts[$key] = $tag->posts_with_tag;
            $counts[$key] = round(log10($tag->posts_with_tag + 1) * 100);
        }
        
        $min_count = min($counts);
        
        $spread = max($counts) - $min_count;
        if ($spread <= 0) {
            $spread = 1;
        }

        $font_spread = $largest - $smallest;
        if ( $font_spread < 0 ) {
            $font_spread = 1;
        }
        $font_step = $font_spread / $spread;

        $a = array();

        foreach ( $tags as $key => $tag ) {
            if ($tag->posts_with_tag == 0) {
                continue;
            }
            $count = $counts[$key];
            $real_count = $real_counts[$key];
            $tag_id = $tags[$key]->ID;
            $tag_name = $tags[$key]->name;
            $tag_link = '/blog/tag/' . $tags[$key]->slug;
            $a[] = "<a href='{$tag_link}' " 
                . "class='tag-link-{$tag_id}'" 
                . "title='{$tag_name}' style='font-size: " .
                round($smallest + (($count - $min_count) * $font_step))
                . "px;'>$tag_name</a>";
        }
            
        //$return = "<ul class='wp-tag-cloud'>\n\t<li>";
        //$return .= join("</li>\n\t<li>", $a);
        //$return .= "</li>\n</ul>\n";
        
        return $a;
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

