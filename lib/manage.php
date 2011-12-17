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
require_once __DIR__ . '/../vendor/simplepie/SimplePieAutoloader.php';

$tpl = new SimpleTemplate(__DIR__ . '/../views/');
$app = new Cling(array('debug' => true));

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
    function($id) use ($app, $tpl)
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
            
        $post->post_date = new DateTime($post->post_date);
        
        $tpl->set("post", $post);
        $content = $tpl->fetch("snippets/manage.edit.txt.php");
        
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
                case "title":
                case "post_status":
                case "original_source":
                case "protected":
                case "post_type":
                case "post_slug":
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
