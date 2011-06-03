<?php 
/**
* Homebrew Website of Claus Beerta
*
* PHP Version 5.3
*
* Copyright (C) <year> by <copyright holders>
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

if ( ! defined('LIMONADE') ) {
    exit('No direct script access allowed');
}

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
        $projects_dir = option('projects_dir');
        
        $glob = "{{$projects_dir}/*.html,{$projects_dir}/*.md}";
        foreach (glob($glob, GLOB_BRACE) as $filename) {
            preg_match('#/(\d+-\d+-\d+) (.*)\.(\w+)$#', $filename, $matches);
            
            if (count($matches) != 4) {
                // FIXME: should output a warning of some sort
                continue;
            }

            if ( !is_null($slug) && $matches[2] != $slug ) {
                continue;
            }

            $content = file($filename);
            
            // Pop the title
            $title = rtrim($content[0]);
            unset($content[0]);
            
            $more = false;
            $teaser = '';
            $post_content = '';
            foreach ($content as $line) {
                if (stripos($line, '<!--more-->') !== false) {
                    $more = true;
                    continue;
                }
                
                if (!$more) {
                    $teaser .= $line;
                } else {
                    $post_content .= $line;
                }
            }
            
            if (in_array($matches[3], array('md','mkd'))) {
                // Markdown
                $teaser = Markdown($teaser);
                $post_content = Markdown($post_content);
            }
            
            $post_date = new DateTime($matches[1]);
            
            $projects[$post_date->format('U')] = (object) array(
                'filename' => $filename,
                'post_slug' => $matches[2],
                'post_title' => $title,
                'post_date' => $post_date,
                'teaser' => $teaser,
                'content' => $post_content,
                'post_content' => $teaser . $post_content,
                'post_type' => 'projects',
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
        
        return array_splice($merged, 0, option('posts_per_page'));
    }
    

    /**
    * Overview of the projectes.
    *
    * @return html
    **/
    public static function overview() 
    {
        set('title', 'Projects');
        set('projects', self::loadProjects());
        set('body', false);
        
        return html('projects.html.php');
    }

    /**
    * Detail on a Project
    *
    * @return html
    **/
    public static function detail() 
    {
        set('title', 'Projects');
        set('projects', self::loadProjects(params('slug')));
        set('body', true);
        
        return html('projects.html.php');
    }
    

    /**
    * Import Projects from Wordpress.
    *
    * @FIXME This is extremely HACKISH. Should it even be in here?
    *           Also assumes my exact posting behavior. which is awesome anyhow.
    * @return void
    **/
    public static function wpImport()
    {
        $dbhost = 'aello.local';
        $dbname = 'claus';
        $dbuser = $_SERVER['DBUSER'];
        $dbpass = $_SERVER['DBPASS'];
        
        $db = mysql_connect($dbhost, $dbuser, $dbpass);
        mysql_select_db($dbname, $db);
        mysql_set_charset('latin1', $db);
        
        $res = mysql_query(
            "
            SELECT *,
            (
                SELECT meta_value 
                FROM `wp_postmeta` 
                WHERE post_id=wp_posts.ID AND 
                meta_key='_thumbnail_id'
            ) AS thumbnail_id,
            (
                SELECT meta_value 
                FROM `wp_postmeta` 
                WHERE post_id=thumbnail_id AND 
                meta_key='_wp_attached_file'
            ) AS thumbnail
            FROM `wp_posts`,`wp_term_relationships`
            WHERE 
                wp_term_relationships.object_id=ID AND
                wp_term_relationships.term_taxonomy_id IN (19,18,14,15)
            "
        );
        while ($data = mysql_fetch_assoc($res)) {

            if (!isset($data['post_date'])) {
                d("no post date: ");
                d($data);
                continue;
            }
            $post_date = new DateTime($data['post_date']);
            
            $content = '';
            
            if ( !empty($data['thumbnail']) ) {
                preg_match("#^(.*)\.(\w+)$#", $data['thumbnail'], $matches);

                $content .= "<a href=\"".option('upload_url') . $matches[0] . "\">";
                $content .= "<img class=\"thumbnail\" src=\"";
                $content .= option('upload_url');
                $content .= $matches[1] . '-150x150.' . $matches[2];
                $content .= "\"></a>";
            }
            $content .= $data['post_content'];
            
            $filename = option('projects_dir') . "/" . 
                $post_date->format('Y-m-d') . 
                " {$data['post_name']}.html";
                
            file_put_contents(
                $filename, 
                utf8_encode($data['post_title'] . "\n\n" . $content)
            );
        }
    }
    
}


