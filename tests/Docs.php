<?php

class DocsTest extends PHPUnit_Framework_TestCase
{
    /**
    * @group docs
    **/
    public function testDocs()
    {
        Docs::index();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|/docs/Help|');
    }

    /**
    * @group docs
    **/
    public function testDocsDetail()
    {
        Docs::index('Help');

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Note that page names may contain spaces|');
    }
}

