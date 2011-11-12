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
* Posts
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
                SELECT GROUP_CONCAT(name) 
                FROM post_terms,term_relations 
                WHERE 
                    term_relations.post_terms_id=post_terms.ID AND
                    term_relations.posts_ID=posts.ID
            ) AS tags,
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
    * Tags Archive
    *
    * @param string $tag    Selected Tag
    * @param int    $offset Offset for Pager
    * @param string $active What Title to activate
    *
    * @return html
    **/
    public static function tag($tag, $offset = 0, $active = 'blog')
    {
        $app = Slim::getInstance();
        $ppp = Helpers::option('posts_per_page');
    
        $app->view()->appendData(
            array(
            'title' => ucfirst($active),
            'active' => $active,
            'ppp' => $ppp,
            'offset' => $offset,
            )
        );
    
        $posts = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_desc('post_date')
            ->limit($ppp)
            ->offset($offset)
            ->where_like('tags', "%{$tag}%");
            
        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();

        if (!$posts) {
            $app->response()->status(404);
            return $app->render('404.html');
        }

        $app->view()->setData('base_url', "/{$active}/tag/{$tag}");
        $app->view()->setData('posts', $posts);
        
        return $app->render('posts/index.html');
    }

    /**
    * Detail on a slug
    *
    * @param string $slug   detail on which slug
    * @param string $active What Title to activate
    *
    * @return html
    **/
    public static function detail($slug, $active = 'blog')
    {
        $app = Slim::getInstance();
        
        $app->view()->appendData(
            array(
            'title' => ucfirst($active),
            'active' => $active,
            )
        );

        $post = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->where_like('post_slug', "%{$slug}%")
            ->order_by_desc('post_date');

        $post = Posts::setPermissions($post);
        $post = $post->find_one();

        if ($post) {            
            $comments = ORM::for_table('comments')
                ->where('post_ID', $post->ID)
                ->order_by_asc('comment_date')
                ->find_many();
        
            $app->view()->setData('comments', $comments);
        } else {
            $app->response()->status(404);
            return $app->render('404.html');
        }
        
        $app->view()->setData('post', $post);
        
        return $app->render('posts/single.html');
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
            //$posts->where_not_equal('post_type', 'photo');
            $posts->where_not_equal('post_type', 'activity');
        } else {
        
        }
        
        return $posts;    
    }


}
