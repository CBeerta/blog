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

if ( ! defined('LIMONADE') ) {
    exit('No direct script access allowed');
}

/**
* Projects
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Blog
{
    /**
    * the Blog
    *
    * @return html
    **/
    public static function index()
    {
        set('title', 'Blog');
        set('sidebar', self::sidebar());
        
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->limit(option('posts_per_page'))
            ->find_many();

        set('posts', $posts);            
        
        return html('blog.html.php');
    }
    
    /**
    * Sidebar Additional Navigation
    *
    * @return html
    **/
    public static function sidebar()
    {

        return partial("snippets/sidebar.blog.html.php");
    }
    /**
    * Detail on a slug
    *
    * @param string $slug Slug of the Post
    *
    * @return html
    **/
    public static function detail($slug = null)
    {
        set('title', 'Blog');
        set('sidebar', self::sidebar());

        $posts = ORM::for_table('posts')
            ->where_like('post_slug', $slug)
            ->order_by_desc('post_date')
            ->limit(option('posts_per_page'))
            ->find_many();

        set('posts', $posts); 
        
        return html('blog.html.php');
    }

    /**
    * Archives
    *
    * @return html
    **/
    public static function archive()
    {
        set('title', 'Blog Archive');
        set('sidebar', self::sidebar());

        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->find_many();
            
        set('posts', $posts);

        return html('archive.html.php');
    }


    /**
    * Return a RSS Feed
    *
    * @return html
    **/
    public static function feed()
    {
        set('build_date', date('r'));

        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->limit(option('posts_per_page'))
            ->find_many();
        $posts = Projects::mergeBlogPosts($posts);
        set('posts', $posts);
        
        return xml('blog.xml.php', null);
    }


    /**
    * Import Projects from Wordpress.
    *
    * @FIXME This is extremely HACKISH. Should it even be in here?
    *           Also assumes my exact posting behavior. which is awesome anyhow.
    * @return void
    **/
    public static function wpImport()
    {
        $dbhost = 'aello.local';
        $dbname = 'claus';
        $dbuser = $_SERVER['DBUSER'];
        $dbpass = $_SERVER['DBPASS'];
        
        $slugs = array();
        $projects = Projects::loadProjects();
        
        foreach ($projects as $post) {
            $slugs[] = $post->post_slug;
        }
        d($slugs);
        
        
        $db = mysql_connect($dbhost, $dbuser, $dbpass);
        mysql_select_db($dbname, $db);
        mysql_set_charset('utf8', $db);
        
        $res = mysql_query(
            "
            SELECT *
            FROM `wp_posts`
            WHERE 
                post_type='post' AND
                post_status='publish'
            "
        );
        
        ORM::for_table('posts')->raw_query('DELETE FROM posts')->find_many();
        while ($data = mysql_fetch_assoc($res)) {
            if (in_array($data['post_name'], $slugs)) {
                // Skip projects
                continue;
            }
            $post = ORM::for_table('posts')->create();
            
            $post->post_date = $data['post_date'];
            $post->post_slug = $data['post_name'];
            $post->post_title = $data['post_title'];
            $post->post_content = iconv(
                'UTF-8', 
                'ISO-8859-1//TRANSLIT//IGNORE', 
                $data['post_content']
            );
            $post->post_content = str_replace(
                'claus.beerta.de/blog/wp-content/plugins/wp-o-matic/cache', 
                'idisk.beerta.net/public/wp-o-matic-cache',
                $post->post_content
            );
            $post->guid = $data['guid'];
            $post->post_status = $data['post_status'];
            
            $post->save();
        }
    }


}




