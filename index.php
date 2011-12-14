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

require_once __DIR__.'/vendor/Slim/Slim/Slim.php';
require_once __DIR__.'/vendor/idiorm/idiorm.php';
require_once __DIR__.'/vendor/markdown/markdown.php';

require_once __DIR__.'/lib/TwigView.php';

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
            return;
        }
    }
}

spl_autoload_register("autoloader");

Helpers::option('templates.path', __DIR__ . '/views/');

$app = new Slim(
    array(
        'view' => 'TwigView',
        'templates.path' => Helpers::option('templates.path'),
        'mode' => 'production',
    )
);

$app->configureMode(
    'production', function() use ($app) {
        $app->config(
            array(
            'log.enable' => false,
            'debug' => false
            )
        );
    }
);

$app->notFound('Other::notFound');

/**
* Load config file and override default options
**/    
$config = parse_ini_file(__DIR__."/config.ini");
foreach ( $config as $k => $v ) {
    Helpers::option($k, $v);
}
ORM::configure('sqlite:' . Helpers::option('dbfile'));
ORM::configure('id_column', 'ID');
ORM::configure('logging', false);

$menu_items = array(
    'projects' => 'Projects',
    'blog' => 'Blog',
    'wallpaper' => 'Wallpaper',
    'photography' => 'Photograpy',
    /* 'docs' => 'Brain Dump', */
    /* 'about' => 'About', */
    /* 'contact' => 'Contact',*/
);

$app->view()->appendData(
    array(
    'menu_items' => $menu_items,
    'header_image'=> Helpers::randomHeaderImage('header-images/'),
    'date_format' => Helpers::option('date_format'),
    'editor' => Helpers::isEditor(),
    'tag_cloud' => Other::tagCloud(),
    'active' => 'projects',
    'title' => null,
    )
);

// Projects related #######################################
$app->get('/projects(/:slug)', 'Projects::overview');

// Blog stuff #############################################
$app->get('^/blog/.*feed.*', 'Posts::feed');
$app->get('/blog', 'Blog::index');
$app->get('/blog/pager/:offset', 'Blog::index');
$app->get('/blog/tag/:tag(/:offset)', 'Blog::tag');
$app->get('/blog/archive', 'Blog::archive');
$app->get('/blog/:slug', 'Blog::detail');

// The editor stuff #######################################
if (Helpers::isEditor()) {
    $app->post('/blog/json_load', 'BlogEdit::loadJSON');
    $app->put('/blog/save/tags', 'BlogEdit::saveTags');
    $app->put('/blog/save', 'BlogEdit::save');
    $app->delete('/blog/trash', 'BlogEdit::trash');
    $app->post('/blog/toggle_publish', 'BlogEdit::togglePublish');
}

// sidebar content. probably ajax #########################
$app->post('/sidebar/search', 'Other::search');

// contact ################################################
$app->get('/contact', 'Contact::index');

// Photography page #######################################
$app->get('/photography', 'Photography::index');
$app->get('/photography/pager/:offset', 'Photography::index');
$app->get('/wallpaper', 'Photography::wallpaper');

// Documentation page #####################################
$app->get('/docs', 'Docs::index');
$app->get('/docs/:slug', 'Docs::index');

// Sitemap ################################################
$app->get('/sitemap.xml', 'Other::sitemap');

// In Development Stuff ###################################
if (Helpers::isEditor()) {
    $app->config('debug', true);
    $app->get('/about', 'Contact::about');
    $app->get('/grid', 'Photography::grid');
}

// And the root of all evil ###############################
$app->get('/', 'Posts::article');

$app->view()->appendData(
    array(
    '_host' => $_SERVER['HTTP_HOST'],
    )
);
$app->run();


