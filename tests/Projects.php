<?php

use \Slim\Slim;

class ProjectsTest extends PHPUnit_Framework_TestCase
{
    public function testProjectOverview()
    {
        Projects::overview();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|This tool allows you tail a or multiple file|');
    }

    public function testProject()
    {
        Projects::overview('webtail-a-tail-for-files-located-on-a-webserver');

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|This tool allows you tail a or multiple file|');
    }
}
