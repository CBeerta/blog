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
    * @param int $offset Offset for pager
    *
    * @return html
    **/
    public static function index($offset = 0)
    {
        $ppp = Slim::config('posts_per_page');
        
        Slim::view()->appendData(
            array(
            'title' => 'Blog',
            'active' => 'blog',
            'ppp' => $ppp,
            'offset' => $offset,
            )
        );
        
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->offset($offset)
            ->limit($ppp);

        if (!Helpers::isEditor()) {
            $posts = $posts->where('post_status', 'publish');
        }
        $posts = $posts->find_many();
        
        Slim::view()->setData('posts', $posts);
        
        return Slim::render('blog/index.html');
    }

    /**
    * Detail on a slug
    *
    * @param string $slug detail on which slug
    *
    * @return html
    **/
    public static function detail($slug = null)
    {
        Slim::view()->appendData(
            array(
            'title' => 'Blog',
            'active' => 'blog',
            )
        );

        $post = ORM::for_table('posts')
            ->where_like('post_slug', "%{$slug}%")
            ->order_by_desc('post_date');

        if (!Helpers::isEditor()) {
            $post = $post->where('post_status', 'publish');
        }
        $post = $post->find_one();

        if ($post) {            
            $comments = ORM::for_table('comments')
                ->where('post_ID', $post->ID)
                ->order_by_desc('comment_date')
                ->find_many();
        
            Slim::view()->setData('comments', $comments);
        } else {
            Slim::response()->status(404);
            return Slim::render('404.html');
        }
        
        Slim::view()->setData('post', $post);
        

        return Slim::render('blog/single.html');
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

        return Slim::response()->body($content);
    }

    /**
    * Save a Post, return html
    *
    * @return json
    **/
    public static function save()
    {
        if (Helpers::isEditor() !== true) {
            return Slim::response()->body('No Permission to edit!');
        }
        
        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;
        $value = isset($_POST['value']) 
            ? $_POST['value'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) || is_null($value) ) {
            return Slim::response()->body('Will not Save!');
        }
        
        $title_match = "|^#\s?(.*?)\n|";
        if (preg_match($title_match, $value, $matches)) {
            $value = trim(preg_replace($title_match, '', $value));
            $post->post_title = trim($matches[1]);
        }

        $post->post_content = $value;
        $post->save();
        
        return Slim::response()->body(Helpers::formatContent($value));
    }

    /**
    * Trash a Post
    *
    * @return partial
    **/
    public static function trash()
    {
        if (Helpers::isEditor() !== true) {
            return Slim::response()->body('No Permission to edit!');
        }

        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) ) {
            return Slim::response()->body('Will not Save!');
        }
        
        $post->delete();

        return Slim::response()->body('Deleted!');
    }

    /**
    * Toggle the Publish status
    *
    * @return partial
    **/
    public static function togglePublish()
    {
        if (Helpers::isEditor() !== true) {
            return Slim::response()->body('No Permission to edit!');
        }

        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) ) {
            return Slim::response()->body('Will not Save!');
        }
        
        $post->post_status = ($post->post_status == 'publish' )
            ? 'draft'
            : 'publish';
        
        $post->save();
        
        return Slim::response()->body($post->post_status);
    }
    
    /**
    * Archives
    *
    * @return html
    **/
    public static function archive()
    {
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date');


        if (!Helpers::isEditor()) {
            $posts = $posts->where('post_status', 'publish');
        }
        
        $posts = $posts->find_many();
        
        Slim::view()->appendData(
            array(
            'title' => 'Blog Archive',
            'active' => 'blog',
            'posts' => $posts,
            )
        );

        return Slim::render('blog/archive.html');
    }


    /**
    * Return a RSS Feed
    *
    * @return html
    **/
    public static function feed()
    {
        $posts = ORM::for_table('posts')
            ->where('post_status', 'publish')
            ->order_by_desc('post_date')
            ->limit(Slim::config('posts_per_page'))
            ->find_many();

        $posts = Projects::mergeBlogPosts($posts);

        Slim::view()->appendData(
            array(
            'posts' => $posts,
            )
        );
        
        Slim::response()->header('Content-Type', 'application/xhtml+xml');        
        return Slim::render('blog/feed.xml');
    }

}


