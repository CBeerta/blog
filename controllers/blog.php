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

}




