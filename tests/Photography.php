<?php

use \Slim\Slim;

class PhotographyTest extends PHPUnit_Framework_TestCase
{
    /**
    * @group photography
    **/
    public function testPhotography()
    {
        Photography::index();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Ettelsberg 2|');
        $this->expectOutputRegex('|picasaweb.google.com/106832871642761506709/MyPhotography#5656660353731572658|');
    }

    /**
    * @group wallpapers
    **/
    public function testWallpapers()
    {
        Photography::wallpapers();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Creative Commons Attribution-NonCommercial-ShareAlike|');
    }
}
