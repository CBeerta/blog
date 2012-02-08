<?php

class ProjectsTest extends PHPUnit_Framework_TestCase
{
    public function testProjectOverview()
    {
        Projects::overview();
    }

    public function testProject()
    {
        Projects::overview('testproject');
    }
}

