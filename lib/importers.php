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

/**
* Debugging shortcut function
*
* @param string $message Message to log
* 
* @return void
**/
function d($message)
{
    if (!is_string($message)) {
        $message = print_r($message, true);
    }
    
    if ( class_exists("WebServer", false) ) {
        WebServer::log($message);
    } else {
        error_log($message);
    }
}


$app = new Cling(array(
    'debug' => true,
    'dry-run' => false,
    'force' => false,
    'templates.path' => __DIR__ . '/../views/',
));

$app->configure(__DIR__ . '/../config.ini');

/**
* Help comes first
**/
$app->command('help', 'h', 
    function() use ($app)
    {
        $app->notFound();
        exit;
    })
    ->help("This Help Text.");

/**
* Enable Dry Run
**/
$app->command('dry-run', 'n',
    function() use ($app)
    {
        echo "dry-run, not saving\n";
        $app->option('dry-run', true);
    });

/**
* Enable Force
**/
$app->command('force', 'f',
    function() use ($app)
    {
        $app->option('force', true);
    });

/**
* Sets a Post Type
**/
$app->command('post-type:',
    function($post_type) use ($app)
    {
        echo "Setting post-type to {$post_type}\n";
        $app->option('post-type', $post_type);
    })
    ->help("Force a post_type. Not All Importers honour this.");

/**
* Import Google+ Posts
**/
$app->command('import-google', 
    function() use ($app)
    {
        $importer = new Import_Google($app);
        $importer->run();
    })
    ->help("Import Google+ Posts.");

/**
* Import Files
**/
$app->command('import-files', 
    function() use ($app)
    {
        $importer = new Import_File($app);
        $importer->run();
    })
    ->help("Import Files from Data Directory.");

/**
* Import Files
**/
$app->command('import-rss:', 
    function($url) use ($app)
    {
        $importer = new Import_Rss($app, $url);
        $importer->run();
    })
    ->help("Import Posts from a RSS Feed.");

/**
* Fix post dates (unify them)
**/
$app->command('fix-post-dates', 
    function($url) use ($app)
    {
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->find_many();
            
        foreach ($posts as $post) {

            print $post->post_date . " -> ";
            
            $parsed = strtotime($post->post_date);
            
            if ($parsed === false) {
                print "Can't Parse!\n";
                continue;
            }
            
            print date('c', $parsed);
            
            if ($post->post_date == date('c', $parsed)) {
                print "... already correct.\n";
                continue;
            }
            
            print "... incorrect. fixing\n";
            
            $post->post_date = date('c', $parsed);
            
            $post->save();
        }
    })
    ->help("Fix Post Dates.");

/**
* Check Links
**/
$app->command('check-links:', 
    function($substr) use ($app)
    {
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->find_many();
            
        foreach ($posts as $post) {
        
            $match = preg_match_all(
                '#(href|src)=["\'](.*?)["\']#i', 
                $post->post_content,
                $matches
            );
            if (!$match) {
                continue;
            }
            
            foreach ($matches[2] as $url) {
                if (stristr($url, $substr) === false) {
                    d("Skipping: " . $url);
                    continue;
                }
            
                d("Checking Article: " . $post->post_slug . ' ID: ' . $post->ID);
                $ch = curl_init();
                curl_setopt(
                    $ch, 
                    CURLOPT_URL, 
                    $url
                );
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

                $ret = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                
                if ($info['http_code'] != 200) {
                    d("!! Failed for URL: " . $info['url']);
                }
            }
        }
    })
    ->help("Check Links for a given Substring.");

/**
* Run the whole thing
**/
$app->run();


