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

require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../vendor/Cling/Cling.php';

$app = new Cling(
    array(
        'debug' => true,
        'template.path' => __DIR__ . '/../views/',
    )
);

$app->configure(__DIR__ . '/../config.ini');

define("EDITOR", isset($_SERVER['EDITOR']) ? $_SERVER['EDITOR'] : 'vi');

/************************************************************************************
* Help comes first
**/
$app->command('help', 'h', 
    function() use ($app)
    {
        $app->notFound();
        exit;
    })
    ->help("This Help Text.");

/************************************************************************************
* Fix the term_relations table after manually fiddling with the database
**/
$app->command('fix-tables',
    function() use ($app)
    {
        echo "Fixing term_relations\n";
        $posts = ORM::for_table('term_relations')
            ->where_raw('posts_id not in (select id from posts)')
            ->find_many();

        foreach ($posts as $post) {
            echo "Deleting {$post->post_terms_ID}\n";
            $post->delete();
        }

        echo "Fixing post_meta\n";
        $posts = ORM::for_table('post_meta')
            ->where_raw('posts_ID not in (select id from posts)')
            ->find_many();

        foreach ($posts as $post) {
            echo "Deleting {$post->post_meta_id}\n";
            $post->delete();
        }
    })->help("Cleanup und delete obsolete Entries from Tables.");

/************************************************************************************
* Show a List of tags currently used
**/
$app->command('taglist',
    function() use ($app)
    {
        $tags = ORM::for_table('post_terms')
            ->select_expr(
                '*, 
                (
                    SELECT COUNT(posts_ID) 
                    FROM term_relations 
                    WHERE term_relations.post_terms_ID=post_terms.ID
                ) AS posts_with_term'
            )->order_by_asc('slug')
            ->find_many();
            
        foreach ($tags as $tag) {
            print "{$tag->name} ({$tag->posts_with_term})\n";
        }

    })
    ->help("Show a Taglist.");

/************************************************************************************
* Limit list display to a single tag
**/
$app->command('tag:', 
    function($tag) use ($app)
    {
        $app->option('tag', $tag);
    })
    ->help("Limit list display to a single tag.");

/************************************************************************************
* List all available posts
**/
$app->command('list', 'l',
    function() use ($app)
    {
        $posts = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_asc('post_date');
        if ($app->option('tag')) {
            $posts = 
                $posts->where_like('tag_names', '%' . $app->option('tag') . '%');
        }
        $posts = $posts->find_many();
            
        foreach ($posts as $post) {
            $flags = array();
            
            !empty($post->original_source)
                ? $flags[] = "i"
                : false;

            !empty($post->protected)
                ? $flags[] = "p"
                : false;

            $flags = implode('', $flags);
            
            $date = new DateTime($post->post_date);
            printf("%-4d %20s [%-4s] %12s: %.60s\n", 
                $post->ID, 
                $date->format('F j, Y'),
                $flags,
                $post->post_type,
                $post->post_title
            );
        }
    })
    ->help("List Available Posts.");

/************************************************************************************
* Delete a Post
**/
$app->command('delete:', 'd:',
    function($id) use ($app)
    {
        if (!is_numeric($id)) {
            $app->notFound();
        }

        $post = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_asc('post_date')
            ->where('ID', $id)
            ->find_one();

        if (!$post) {
            echo "Post Not Found\n";
        } else {
            $post->delete();
        }
    })
    ->help("Delete a Post.");

/************************************************************************************
* Show details on a post
**/
$app->command('detail:', 'v:',
    function($id) use ($app)
    {
        if (!is_numeric($id)) {
            $app->notFound();
        }
        
        $post = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_asc('post_date')
            ->where('ID', $id)
            ->find_one();

        if (!$post) {
            exit;
        }

        foreach ($post->as_array() as $k=>$v) {
            $v = str_replace("\n", " ", $v);
            
            printf("%16s: %s\n", $k, $v);
        }
    })
    ->help("Show a Post.");

/************************************************************************************
* Edit a post
**/
$app->command('edit:', 'e:',
    function($id) use ($app)
    {
        if (!is_numeric($id)) {
            $app->notFound();
        }
        
        $post = ORM::for_table('posts')
            ->select_expr(Posts::_POSTS_SELECT_EXPR)
            ->order_by_asc('post_date')
            ->where('ID', $id)
            ->find_one();

        if (!$post) {
            $post = ORM::for_table('posts')->create();
            $post->post_date = new DateTime("now");
            $post->post_status = 'draft';
            $post->post_type = 'blog';
            $post->protected = 0;
        } else {
            $post->post_date = new DateTime($post->post_date);
        }
        
        if (function_exists('tidy_parse_string')) {
            /*
            $tidy = tidy_parse_string($post->post_content);
            $post->post_content = $tidy->body();
            */
        }
        
        $app->view->set("post", $post);
        $content = $app->view->fetch("snippets/manage.edit.txt.php");
        
        $tmpname = tempnam("/var/tmp", "page-edit-{$id}-");
        file_put_contents($tmpname, $content);
        
        passthru(EDITOR . " {$tmpname} > $(tty)", $ret);
        
        if (md5($content) != md5_file($tmpname)) {

            print "Storing\n";

            $content = '';
            foreach (file($tmpname) as $line) {
            
                if (!preg_match("|^#--\s*(.+?):\s*(.*)$|", $line, $matches)) {
                    $content .= $line;
                    continue;
                }
                
                list($nil, $key, $value) = $matches;
                
                switch($key) {
                case "post_title":
                case "post_status":
                case "original_source":
                case "protected":
                case "post_type":
                    $post->$key = trim($value);
                    break;
                case "post_slug":
                    if (empty($value)) {
                        $value = Helpers::buildSlug($post->post_title);
                    }
                    $post->$key = trim($value);
                    break;
                case "post_date":
                    $date = new DateTime($value);
                    $post->post_date = $date->format('c');
                    break;
                case "tags":
                    $tags = explode(',', $value);
                    Helpers::addTags($tags, $post->ID);
                    break;
                default:
                    break;
                }
                
            }
            $post->post_content = trim($content);
            $post->save();
        }
        unlink($tmpname);
    })
    ->help("Edit a Post.");

$app->run();
