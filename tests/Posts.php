<?php

require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/InitTests.php';

class PostTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {

        $_SERVER['REQUEST_METHOD'] = "GET";
        $_ENV['SLIM_MODE'] = null;
        $_COOKIE['foo'] = 'bar';
        $_COOKIE['foo2'] = 'bar2';
        $_SERVER['REQUEST_URI'] = "/";

        $app = new Slim(
            array(
                'view' => 'CustomView',
                'templates.path' => Helpers::option('templates.path'),
                'mode' => 'testing',
            )
        );

        $app->configureMode(
            'production', function() use ($app) {
                $app->config(
                    array(
                    'log.enable' => false,
                    'debug' => true
                    )
                );
            }
        );
    }
    
    public function testArticle()
    {
        Posts::article();
    }

    public function testBlog()
    {
        Blog::index();
    }

    public function testTag()
    {
        Blog::tag('deviantart');
    }

    public function testPost()
    {
        Blog::detail('testpost');
    }

    public function testFeed()
    {
        Posts::feed();
    }

}


