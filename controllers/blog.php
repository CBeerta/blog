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
        set('active', 'blog');
        
        $ppp = option('posts_per_page');
        set('ppp', $ppp);
        $offset = set_or_default('offset', params('offset'), 0);

        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->offset($offset)
            ->limit($ppp);

        if (!isEditor()) {
            $posts = $posts->where('post_status', 'publish');
        }
        $posts = $posts->find_many();
        
        set('posts', $posts);            
        
        return html('blog/index.html.php');
    }

    /**
    * Photography Page
    *
    * @return html
    **/
    public static function photography()
    {
        set('title', 'Photography');
        set('active', 'photography');
        
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->where('post_type', 'flickr');

        if (!isEditor()) {
            $posts = $posts->where('post_status', 'publish');
        }
        $posts = $posts->find_many();
        
        set('posts', $posts);            
        
        return html('blog/index.html.php');
    }
    
    /**
    * Detail on a slug
    *
    * @return html
    **/
    public static function detail()
    {
        $slug = params('slug');
        
        set('title', 'Blog');
        set('active', 'blog');

        $post = ORM::for_table('posts')
            ->where_like('post_slug', "%{$slug}%")
            ->order_by_desc('post_date');

        if (!isEditor()) {
            $post = $post->where('post_status', 'publish');
        }
        $post = $post->find_one();

        if ($post) {            
            $comments = ORM::for_table('comments')
                ->where('post_ID', $post->ID)
                ->order_by_desc('comment_date')
                ->find_many();
        
            set('comments', $comments);            
        }
        
        set('post', $post);
        
        return html('blog/single.html.php');
    }

    /**
    * Load a Post, return as json
    *
    * @return json
    **/
    public static function loadJSON()
    {
        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;
            
        $post = ORM::for_table('posts')->find_one($id);
        
        $content = '# ' . $post->post_title . "\n\n" . $post->post_content;
        return partial($content);
    }

    /**
    * Save a Post, return html
    *
    * @return json
    **/
    public static function save()
    {
        if (isEditor() !== true) {
            return partial('No Permission to edit!');
        }
        
        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;
        $value = isset($_POST['value']) 
            ? $_POST['value'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) || is_null($value) ) {
            //d($_POST);
            return partial('Will not Save!');
        }
        
        $title_match = "|^#\s?(.*?)\n|";
        if (preg_match($title_match, $value, $matches)) {
            $value = trim(preg_replace($title_match, '', $value));
            $post->post_title = trim($matches[1]);
        }

        $post->post_content = $value;
        $post->save();
        
        return partial(formatContent($value));
    }

    /**
    * Trash a Post
    *
    * @return partial
    **/
    public static function trash()
    {
        if (isEditor() !== true) {
            return partial('No Permission to edit!');
        }

        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) ) {
            return partial('Will not Save!');
        }
        
        $post->delete();

        return partial('Deleted!');
    }

    /**
    * Toggle the Publish status
    *
    * @return partial
    **/
    public static function togglePublish()
    {
        if (isEditor() !== true) {
            return partial('No Permission to edit!');
        }

        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) ) {
            return partial('Will not Save!');
        }
        
        $post->post_status = ($post->post_status == 'publish' )
            ? 'draft'
            : 'publish';
        
        $post->save();
        
        return partial($post->post_status);
    }
    
    /**
    * Archives
    *
    * @return html
    **/
    public static function archive()
    {
        set('title', 'Blog Archive');
        set('active', 'blog');

        $posts = ORM::for_table('posts')
            ->where('post_status', 'publish')
            ->order_by_desc('post_date')
            ->find_many();
            
        set('posts', $posts);

        return html('blog/archive.html.php');
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
            ->where('post_status', 'publish')
            ->order_by_desc('post_date')
            ->limit(option('posts_per_page'))
            ->find_many();
        $posts = Projects::mergeBlogPosts($posts);
        set('posts', $posts);
        
        return xml('blog/feed.xml.php', null);
    }

}


