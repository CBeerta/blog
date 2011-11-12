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
* Photography
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Photography
{

    /**
    * Photography Page
    *
    * @return html
    **/
    public static function index()
    {
        return Blog::tag('photo', 'photography');
    }

    /**
    * Page for wallpapers
    *
    * FIXME: stuff is beeing encoded into the guid which is wierd
    *        should really add a table to the database for additional metadata
    *
    * @return html
    **/
    public static function wallpaper()
    {
        $app = Slim::getInstance();
        $public_url = Helpers::option('public_url');
    
        $posts = ORM::for_table('posts')
            ->select_expr(Blog::_POSTS_SELECT_EXPR)
            ->where_like('tags', "%Wallpaper%")
            ->order_by_desc('post_date')
            ->where_equal('post_type', 'photo');

        $posts = $posts->find_many();

        $dimensions = array();
        $filenames = array();

        foreach ($posts as $post) {

            $metas = ORM::for_table('post_meta')
                ->where('posts_ID', $post->ID);
            $metas = $metas->find_many();
            
            foreach ($metas as $meta) {
                switch ($meta->meta_key) {
                case 'photo_width':
                    $dimensions[$post->guid]['width'] = $meta->meta_value;
                    break;
                case 'photo_height':
                    $dimensions[$post->guid]['height'] = $meta->meta_value;
                    break;
                case 'filename':
                    $filenames[$post->guid] = $meta->meta_value;
                }
            }

        }
        
        $app->view()->appendData(
            array(
            'title' => 'Wallpaper',
            'active' => 'wallpaper',
            'public_url' => $public_url,
            'dimensions' => $dimensions,
            'filenames' => $filenames,
            'posts' => $posts,
            )
        );
        
        return $app->render('wallpaper.html');
    }

    /**
    * Grid layout of images
    *
    * @return html
    **/
    public static function grid()
    {
        $app = Slim::getInstance();
        $public_dir = Helpers::option('public_loc');
        $public_url = Helpers::option('public_url');
        
        $glob = "{{$public_dir}/square_thumb_*.jpg}";
        foreach (glob($glob, GLOB_BRACE) as $filename) {
            $square_thumb[] = basename($filename);
        }

        $posts = ORM::for_table('posts')
            ->select_expr(Blog::_POSTS_SELECT_EXPR)
            ->order_by_desc('post_date')
            ->where_equal('post_type', 'photo');

        if (!Helpers::isEditor()) {
            $posts = $posts->where('post_status', 'publish');
        }
        $posts = $posts->find_many();
        
        $app->view()->appendData(
            array(
            'title' => 'Photography',
            'active' => 'photography',
            'posts' => $posts,
            )
        );
        
        return $app->render('photogrid.html');
    }

}



