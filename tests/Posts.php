<?php

class PostsTest extends PHPUnit_Framework_TestCase
{
    /**
    * @group articles
    **/
    public function testArticle()
    {
        Posts::article();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Lorem ipsum dolor sit amet|');
    }

    /**
    * @group blog
    **/
    public function testBlog()
    {
        Blog::index();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Lorem ipsum dolor sit amet|');
    }

    /**
    * @group blog
    **/
    public function testBlogPost()
    {
        Blog::detail('test');

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Lorem ipsum dolor sit amet|');
    }

    /**
    * @group blog
    **/
    public function testBlogPostNonExisting()
    {
        Blog::detail('testthatdoesnotexist');

        $app = Slim::getInstance();
        $this->assertEquals(404, $app->response()->status());
        $this->expectOutputRegex('|Sorry. Couldn\'t Find That Page!|');

        // reset status for followup tests
        $app->response()->status(200);
    }

    /**
    * @group blog
    **/
    public function testBlogArchive()
    {
        Blog::archive();

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Blog Archive|');
    }

    /**
    * @group blog
    **/
    public function testTag()
    {
        Blog::tag('test');

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Lorem ipsum dolor sit amet|');
    }

    /**
    * @group blog
    **/
    public function testPost()
    {
        Blog::detail('test');

        $app = Slim::getInstance();
        $this->assertEquals(200, $app->response()->status());
        $this->expectOutputRegex('|Lorem ipsum dolor sit amet|');
    }

     /**
    * @group blog
    **/
   public function testFeed()
    {
        Posts::feed();

        $app = Slim::getInstance();
        $this->assertEquals('application/rss+xml', $app->response()->header('Content-Type'));
        $this->expectOutputRegex('|content:encoded|');
        $this->assertEquals(200, $app->response()->status());
    }
}
