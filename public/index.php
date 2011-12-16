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

// Configure Menu Items ###################################
$menu_items = array(
    'projects' => 'Projects',
    'blog' => 'Blog',
    'wallpapers' => 'Wallpapers',
    'photography' => 'Photograpy',
    /* 'docs' => 'Brain Dump', */
    'about' => 'About',
);
$app->view()->appendData(array('menu_items' => $menu_items));

// Projects related #######################################
$app->get('/projects(/:slug)', 'Projects::overview');

// Blog stuff #############################################
$app->get('^/blog/.*feed.*', 'Posts::feed');
$app->get('/blog', 'Blog::index');
$app->get('/blog/pager/:offset', 'Blog::index');
$app->get('/blog/tag/:tag(/:offset)', 'Blog::tag');
$app->get('/blog/archive', 'Blog::archive');
$app->get('/blog/:slug', 'Blog::detail');

// sidebar content. probably ajax #########################
$app->post('/sidebar/search', 'Other::search');

// contact ################################################
$app->get('/contact', 'Contact::about');
$app->get('/about', 'Contact::about');

// Photography page #######################################
$app->get('/photography', 'Photography::index');
$app->get('/photography/pager/:offset', 'Photography::index');
$app->get('/wallpaper.*', 'Photography::wallpapers');

// Documentation page #####################################
$app->get('/docs', 'Docs::index');
$app->get('/docs/:slug', 'Docs::index');

// Sitemap ################################################
$app->get('/sitemap.xml', 'Other::sitemap');

// In Development Stuff ###################################
if (Helpers::isEditor()) {
    $app->config('debug', true);
}

// And the root of all evil ###############################
$app->get('/', 'Posts::article');

$app->run();

