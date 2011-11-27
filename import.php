#!/usr/bin/env php
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

require_once __DIR__ . '/vendor/Cling/Cling.php';
require_once __DIR__ . '/vendor/idiorm/idiorm.php';

require_once __DIR__ . '/controllers/importers.php';

/**
* Autoloader for helpers and controllers
*
* @param string $class A Class file that is needed
*
* @return void
**/
function autoloader($class)
{
    $directories = array('/controllers/importers/', '/lib/');
    
    foreach ($directories as $dir) {
        if (file_exists(__DIR__ . $dir . strtolower($class) . '.php')) {
            include_once __DIR__ . $dir . strtolower($class) . '.php';
            return;
        }
        if (file_exists(__DIR__ . $dir . $class . '.php')) {
            include_once __DIR__ . $dir . $class . '.php';
            return;
        }
    }
}

spl_autoload_register("autoloader");


$app = new Cling(array(
    'debug' => true,
    'dry-run' => false,
    'force' => false,
    'templates.path' => __DIR__ . '/views/',
));

$app->configure(__DIR__ . '/config.ini');

ORM::configure('sqlite:' . $app->option('dbfile'));
ORM::configure('id_column', 'ID');
ORM::configure('logging', false);

/**
* Help comes first
* FIXME: integrate into Cling?
**/
$app->command('help', 'h', 
    function() use ($app)
    {
        print $app;
        exit;
    })
    ->help("This Help Text.");

/**
* Enable Dry Run
**/
$app->command('dry-run', 'n',
    function() use ($app)
    {
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
* Run the whole thing
**/
$app->run();


