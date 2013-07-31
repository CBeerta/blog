<?php

use \Slim\Slim;

class OtherTest extends PHPUnit_Framework_TestCase
{
    /**
    * @group other
    **/
    public function testSitemap()
    {
        Other::sitemap();

        $app = Slim::getInstance();
        $this->assertEquals('application/xml', $app->response()->header('Content-Type'));
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|loc>.*//blog/test</loc>|');
    }

    /**
    * @group other
    **/
    public function testSearch()
    {
        Other::search();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Lorem ipsum dolor sit amet|');
    }

    /**
    * @group other
    **/
    public function testTagCloud()
    {
        $tagcloud = Other::tagCloud();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->assertInternalType('array', $tagcloud);
        $this->assertTrue( count($tagcloud) >= 2 );
    }
}

