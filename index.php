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

require_once __DIR__.'/vendor/Slim/Slim/Slim.php';
require_once __DIR__.'/lib/TwigView.php';
require_once __DIR__.'/vendor/idiorm/idiorm.php';
require_once __DIR__.'/vendor/markdown/markdown.php';

/**
* Autoloader for helpers and controllers
*
* @param string $class A Class file that is needed
*
* @return void
**/
function autoloader($class)
{
    $directories = array('/controllers/', '/lib/');
    
    foreach ($directories as $dir) {
        if (file_exists(__DIR__ . $dir . strtolower($class) . '.php')) {
            include_once __DIR__ . $dir . strtolower($class) . '.php';
        }
    }
}

spl_autoload_register("autoloader");

Slim::init(
    array(
    'view' => 'TwigView',
    'templates.path' => __DIR__ . '/views/',
    )
);

Slim::configureMode(
    'production', function() {
        Slim::config(
            array(
            'log.enable' => false,
            'debug' => false
            )
        );
    }
);

Slim::notFound('Helpers::notFound');

/**
* Options with defaults, overridable in config.ini
**/
$options = array (
    'cache_dir' => '/var/tmp/',
    'dbfile' => './data/planner.db',
    'projects_dir' => './data/projects',
    'docs_dir' => './data/docs',
    'posts_dir' => './data/posts',
    'public_url' => 'http://localhost/data/uploads',
    'public_loc' => './data/uploads',
    'deviantart_items' => 4,
    'posts_per_page' => 10,
    'date_format' => 'D, d M Y',
    'base_uri' => '/',
    'public_dir' => __DIR__ . '/public/',
);

/**
* Load config file and override default options
**/    
$config = parse_ini_file(__DIR__."/config.ini");
foreach ( $options as $k => $v ) {
    $v = isset($config[$k]) ? $config[$k] : $options[$k];
    Slim::config($k, $v);
}

ORM::configure('sqlite:' . Slim::config('dbfile'));
ORM::configure('id_column', 'ID');
ORM::configure('logging', false);

$menu_items = array(
    'projects' => 'Projects',
    'blog' => 'Blog',
    'photography' => 'Photograpy',
    /* 'docs' => 'Brain Dump', */
    /* 'about' => 'About', */
    /*'contact' => 'Contact',*/
);

Slim::view()->setData(
    array(
    'menu_items' => $menu_items,
    'header_image'=> Helpers::randomHeaderImage('header-images/'),
    'date_format' => Slim::config('date_format'),
    'editor' => Helpers::isEditor(),
    )
);

// Projects related #######################################
Slim::get('/projects(/:slug)', 'Projects::overview');

// Blog stuff #############################################
Slim::get('^/blog/.*feed.*', 'Blog::feed');
Slim::get('/blog', 'Blog::index');
Slim::get('/blog/pager/:offset', 'Blog::index');
Slim::get('/blog/tag/:tag(/:offset)', 'Blog::index');
Slim::get('/blog/archive', 'Blog::archive');
Slim::get('/blog/:slug', 'Blog::detail');

// The editor stuff #######################################
if (Helpers::isEditor()) {
    Slim::post('/blog/json_load', 'Blog::loadJSON');
    Slim::put('/blog/save/tags', 'Blog::saveTags');
    Slim::put('/blog/save', 'Blog::save');
    Slim::delete('/blog/trash', 'Blog::trash');
    Slim::post('/blog/toggle_publish', 'Blog::togglePublish');
}

// sidebar content. probably ajax #########################
Slim::post('/sidebar/search', 'Other::search');

// contact ################################################
Slim::get('/contact', 'Contact::index');
//Slim::get('/about', 'Contact::about');

// Photography page #######################################
Slim::get('/photography', 'Photography::index');

// Documentation page #####################################
Slim::get('/docs', 'Docs::index');
Slim::get('/docs/:slug', 'Docs::index');

// Sitemap ################################################
Slim::get('/sitemap.xml', 'Other::sitemap');

// And the root of all evil ###############################
Slim::get('/', 'Projects::overview');

if (PHP_SAPI == 'cli') {
    // Need to manually load here, as we'll skip the run();
    include_once __DIR__.'/controllers/importers.php';
    Importers::parseArgs();
} else {
    Slim::run();
}


