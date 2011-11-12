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
* Blog
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
    * @param int $offset Offset when there is a tag selected
    *
    * @return html
    **/
    public static function index($offset = 0)
    {
        $app = Slim::getInstance();
        $ppp = Helpers::option('posts_per_page');

        $app->view()->appendData(
            array(
            'title' => 'Blog',
            'active' => 'blog',
            'ppp' => $ppp,
            'offset' => $offset,
            )
        );

        $posts = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_desc('post_date')
            ->limit($ppp)
            ->offset($offset);
        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();

        if (!$posts) {
            $app->response()->status(404);
            return $app->render('404.html');
        }
        
        $app->view()->setData('base_url', "/blog/pager");
        $app->view()->setData('posts', $posts);
        
        return $app->render('posts/index.html');
    }

    /**
    * Archives
    *
    * @return html
    **/
    public static function archive()
    {
        $app = Slim::getInstance();
        
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date');

        $posts = Posts::setPermissions($posts);
        $posts = $posts->find_many();

        $tags = ORM::for_table('post_terms')
            ->select_expr(
                '
                *,
                (
                    SELECT COUNT(ID) FROM `posts`, `term_relations`
                    WHERE 
                        term_relations.post_terms_ID=post_terms.ID AND
                        term_relations.posts_ID=posts.ID
                ) AS posts_with_tag
                '
            )
            ->order_by_asc('slug')
            ->find_many();

        $app->view()->appendData(
            array(
            'title' => 'Blog Archive',
            'active' => 'blog',
            'posts' => $posts,
            'tags' => $tags,
            )
        );

        return $app->render('posts/archive.html');
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
        return $app->render('posts/feed.xml');
    }

}


