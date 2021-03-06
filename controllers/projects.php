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

use Slim\Slim;
use Michelf\Markdown;

/**
* Projects
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Projects
{
    /**
    * Load all Available Projects and render them
    *
    * @param string $slug Slug to load, otherwise all
    *
    * @return array All projects
    **/
    public static function loadProjects($slug = null)
    {
        $projects = array();
        $projects_dir = Helpers::option('projects_dir');
        
        $glob = "{{$projects_dir}/*.html,{$projects_dir}/*.mkd}";
        foreach (glob($glob, GLOB_BRACE) as $filename) {
            preg_match('#/(\d+-\d+-\d+) (.*)\.(\w+)$#', $filename, $matches);
            
            if (count($matches) != 4) {
                continue;
            }

            if ( !is_null($slug) && strtolower($matches[2]) != strtolower($slug) ) {
                continue;
            }

            $content = file($filename);
            $extension = $matches[3];
            $post_slug = $matches[2];
            $post_date = $matches[1];
            
            // Pop the title
            $title = rtrim($content[0]);
            unset($content[0]);
            
            $more = false;
            $teaser = '';
            $post_content = '';
            $github_project = false;
            foreach ($content as $line) {
                if (stripos($line, '<!--more-->') !== false) {
                    $more = true;
                    continue;
                }
                
                if (preg_match('|.*github.com/CBeerta/(\w+)|', $line, $matches)) {
                    $github_project = $matches[1];
                }
                
                if (!$more) {
                    $teaser .= $line;
                } else {
                    $post_content .= $line;
                }
            }
            
            if (in_array($extension, array('md','mkd'))) {
                // Markdown
                $teaser = Markdown::defaultTransform($teaser);
                $post_content = Markdown::defaultTransform($post_content);
            }
            
            $post_date = new DateTime($post_date);
            
            $projects[$post_date->format('U')] = (object) array(
                'ID' => md5($filename),
                'filename' => $filename,
                'post_slug' => $post_slug,
                'post_title' => $title,
                'post_date' => $post_date,
                'teaser' => $teaser,
                'content' => $post_content,
                'post_content' => $teaser . $post_content,
                'github_project' => $github_project,
                'post_type' => 'project',
            );
        }
        
        // Sort by timestamp, newest first
        krsort($projects);
        return $projects;
    }

    /**
    * Merge blog posts with our projects, ordered by date
    *
    * @param object $posts An Idiorm with blog posts to merge
    *
    * @return object Merged Object with blog posts and projects
    **/
    public static function mergeBlogPosts($posts)
    {
        $slugs = array();
        $merged = self::loadProjects();
        
        foreach ($merged as $post) {
            $slugs[] = $post->post_slug;
        }

        foreach ($posts as $post) {
            if (in_array($post->post_slug, $slugs)) {
                // Skip blog entries that we already have as projects
                continue;
            }

            $post_date = new DateTime($post->post_date);
            
            $merged[$post_date->format('U')] = (object) array (
                'ID' => $post->ID,
                'post_date' => $post_date,
                'post_title' => $post->post_title,
                'post_slug' => $post->post_slug,
                'post_content' => $post->post_content,
                'guid' => $post->guid,
                'post_status' => $post->post_status,
                'post_type' => 'blog',
            );
        }
        krsort($merged);
        return $merged;
    }
    

    /**
    * Overview of the projectes.
    *
    * @param string $slug A Specific post to pull
    *
    * @return html
    **/
    public static function overview($slug = null) 
    {
        $app = Slim::getInstance();
        $projects = self::loadProjects($slug);
        
        if (empty($projects)) {
            $app->response()->status(404);
            return $app->render('404.html');
        }
        
        if (!is_null($slug) && count($projects) == 1) { 
            $project = current($projects);
            $app->view()->appendData(
                array(
                    'page_title' => $project->post_title,
                )
            );
        }
        
        $app->view()->appendData(
            array(
            'active' => 'projects',
            'projects' => $projects,
            'show_body' => $slug,
            )
        );
        
        return $app->render('projects.html');
    }
    
}


