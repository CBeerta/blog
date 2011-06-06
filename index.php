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

require_once __DIR__.'/vendor/limonade/lib/limonade.php';
require_once __DIR__.'/vendor/idiorm/idiorm.php';
require_once __DIR__.'/vendor/markdown/markdown.php';
require_once __DIR__.'/vendor/simplepie/SimplePieAutoloader.php';

require_once __DIR__.'/lib/helpers.php';

/**
* Limonades Configure Abstract. Configure some Basics
*
* @return void
**/
function configure () 
{
    /**
    * Options with defaults, overridable in config.ini
    **/
    $options = array (
        'cache_dir' => '/var/tmp/',
        'dbfile' => './data/planner.db',
        'projects_dir' => './data/projects',
        'public_url' => 'http://localhost/data/uploads',
        'public_loc' => './data/uploads',
        'deviantart_items' => 4,
        'posts_per_page' => 10,
        'date_format' => 'D, d M Y',
        'base_uri' => '/',
        'env' => ENV_PRODUCTION,
        );
        
    if (PHP_SAPI == 'cli') {
        $options['env'] = ENV_DEVELOPMENT;
        $option['host'] = 'none';
    } else {
        $option['host'] = 'http://' . $_SERVER['HTTP_HOST'] . '/';
    }

    /**
    * Load config file and override default options
    **/    
    $config = parse_ini_file(__DIR__."/config.ini");
    foreach ( $options as $k => $v ) {
        $v = isset($config[$k]) ? $config[$k] : $options[$k];
        option($k, $v);
    }

    ORM::configure('sqlite:' . option('dbfile'));
    ORM::configure('id_column', 'ID');
    ORM::configure('logging', false);
}

/**
* Not Found Handler
*
* @param int    $errno  Error Number
* @param string $errstr Error string
*
* @return void
**/
function not_found($errno, $errstr) 
{
    // FIXME: This should redirect to a search and stuff
    set('errno', $errno);
    set('errstr', $errstr);
    return render("404.html.php", null);
}

$menu_items = array(
    'projects' => 'Projects',
    'blog' => 'Blog',
    'photography' => 'Photograpy',
    'contact' => 'Contact',
);

set('menu_items', $menu_items);

layout('base.html.php');

// Projects related #######################################
dispatch_get('/projects', 'Projects::overview');
dispatch_get('/projects/:slug', 'Projects::detail');

// Blog stuff #############################################
dispatch_get('^/blog/(.*feed.*)', 'Blog::feed');
dispatch_get('/blog', 'Blog::index');
dispatch_get('/blog/pager/:offset', 'Blog::index');
dispatch_get('/blog/archive', 'Blog::archive');
dispatch_get('/blog/:slug', 'Blog::detail');

// These are here for compatibility with the previous WP install
dispatch_get('/blog/:year/:month/:slug/', 'Blog::detail');

// The editor stuff #######################################
dispatch_post('/blog/json_load', 'Blog::loadJSON');
dispatch_post('/blog/save', 'Blog::save');
dispatch_post('/blog/trash', 'Blog::trash');
dispatch_post('/blog/toggle_publish', 'Blog::togglePublish');

// sidebar content. probably ajax #########################
//dispatch_get('/sidebar/github/:username', 'Sidebar::github');
//dispatch_get('/sidebar/deviantart/:search', 'Sidebar::deviantart');
dispatch_post('/sidebar/search', 'Sidebar::search');

// contact ################################################
dispatch_get('/contact', 'Contact::index');

// Redirect photography to fluidr #########################
dispatch_get('/photography', 'Blog::photography');

/**
* Redirect to fluidr
*
* @return void
**/
function photography() 
{
    redirect_to('http://www.fluidr.com/photos/cbeerta/only-photos');
}

// And the root of all evil ###############################
dispatch_get('/', 'Projects::overview');

if (PHP_SAPI == 'cli') {
    // Need to manually load here, as we'll skip the run();
    include_once __DIR__.'/controllers/importers.php';
    Importers::parseArgs();
    include_once __DIR__.'/vendor/limonade/lib/lemon_server.php';
} else {
    run();
}


