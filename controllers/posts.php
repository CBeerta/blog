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

/**
* Posts - Functions that are more global and used in all parts of the Site
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Posts
{
    /**
    * Select Expression for Posts for Idiorm
    **/
    const _POSTS_SELECT_EXPR = "
            *,
            (
                SELECT COUNT(ID)
                FROM comments
                WHERE
                    comments.post_ID=posts.ID
            ) AS comment_count,
            (
                SELECT GROUP_CONCAT(post_terms.slug) 
                FROM post_terms,term_relations 
                WHERE 
                    term_relations.post_terms_id=post_terms.ID AND
                    term_relations.posts_ID=posts.ID
            ) AS tags,
            (
                SELECT GROUP_CONCAT(post_terms.name) 
                FROM post_terms,term_relations 
                WHERE 
                    term_relations.post_terms_id=post_terms.ID AND
                    term_relations.posts_ID=posts.ID
            ) AS tag_names,
            (
                SELECT GROUP_CONCAT(meta_key || '|' || meta_value)
                FROM post_meta 
                WHERE
                    post_meta.posts_ID=posts.ID
                ORDER BY
                    meta_key
            )  AS post_meta
            ";

    /**
    * Posts Index. Used by the Blog and Photography
    *
    * @param int    $offset    Offset when there is a tag selected
    * @param string $active    Which subsection to show as active
    * @param array  $post_type post types to select
    *
    * @return html
    **/
    public static function index($offset, $active, $post_type)
    {
        $app = Slim::getInstance();
        $ppp = Helpers::option('posts_per_page');

        $app->view()->appendData(
            array(
            'active' => $active,
            'ppp' => $ppp,
            'offset' => $offset,
            )
        );

        $posts = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_desc('post_date')
            ->where_in('post_type', $post_type)
            ->limit($ppp)
            ->offset($offset);
        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();

        if (!$posts) {
            $app->response()->status(404);
            return $app->render('404.html');
        }
        
        $app->view()->setData('base_url', "/{$active}/pager");
        $app->view()->setData('posts', $posts);
        
        return $app->render('blog/index.html');
    }

    /**
    * Landing Page with an Article to display (can be Project or Blog)
    *
    * @return html
    **/
    public static function article()
    {
        $app = Slim::getInstance();

        $posts = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_desc('post_date')
            ->limit(1)
            ->where_like('tags', "%Article%");
            
        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();
        
        $posts = Projects::mergeBlogPosts($posts);
        
        $post = array_shift($posts);
        
        $app->view()->appendData(
            array(
            'post' => $post,
            )
        );

        return $app->render('blog/article.html');
    }

    /**
    * Return a RSS Feed
    *
    * @return xml
    **/
    public static function feed()
    {
        $app = Slim::getInstance();
        
        $posts = ORM::for_table('posts')
            ->where('post_status', 'publish')
            ->order_by_desc('post_date')
            ->limit(Helpers::option('posts_per_page'))
            ->where_equal('post_type', 'blog')
            ->find_many();

        $posts = Projects::mergeBlogPosts($posts);
        $posts = array_splice($posts, 0, Helpers::option('posts_per_page'));

        $app->view()->appendData(
            array(
            'posts' => $posts,
            )
        );
        
        $app->response()->header('Content-Type', 'application/rss+xml');        
        return $app->render('blog/feed.xml');
    }

    /**
    * Split meta fields into an object
    *
    * @param array $post_meta Meta Data
    *
    * @return object
    **/
    public static function splitMeta($post_meta)
    {
        $ret = (object) array();
    
        foreach (explode(',', $post_meta) as $meta) {
            list($k, $v) = explode('|', $meta);
            
            if (empty($k) || empty($v)) {
                continue;
            }
            
            $ret->$k = $v;
        }
        return $ret;
    }


    /**
    * Set Permissions on Posts ORM
    *
    * @param object $posts posts ORM object
    *
    * @return object
    **/
    public static function setPermissions($posts)
    {
        if (!Helpers::isEditor()) {
            $posts->where('post_status', 'publish');
            $posts->where_not_equal('post_type', 'activity');
        } else {
        
        }
        return $posts;    
    }


}
