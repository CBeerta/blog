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

if ( PHP_SAPI != 'cli' ) {
    // dont do anything if we're not a cli php
    return;
}

require_once __DIR__ . '/../vendor/simplepie/SimplePieAutoloader.php';


/**
* Debugging shortcut function
*
* @param string $message Message to log
* 
* @return void
**/
function d($message)
{
    if (!is_string($message)) {
        $message = print_r($message, true);
    }
    
    if ( class_exists("WebServer", false) ) {
        WebServer::log($message);
    } else {
        error_log($message);
    }
}


/**
* Importer
*
* Empty Base class for an Importer
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Importer
{
    /**
    * Value from Command line Argument
    **/
    protected $value = null;
    
    /**
    * Dryrun?
    **/
    protected $dryrun = true;
    
    /**
    * force overwrites?
    **/
    protected $force = false;
    
    /**
    * Setup the importer
    *
    * @param string $value  Any Command line Argument
    * @param boot   $dryrun Dryrun
    * @param boot   $force  Force Overwrites
    *
    * @return self
    **/
    public function setup($value, $dryrun, $force)
    {
        $this->value = $value;
        $this->dryrun = $dryrun;
        $this->force = $force;

        return $this;
    }
    
    /**
    * Run the importer
    *
    * @return bool
    **/
    public function run()
    {
        return false;
    }
}

/**
* Importers
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Importers
{
    /**
    * API Token for posterous api
    **/
    private static $_posterous_api_token = false;
    
    /**
    * Commands that we understand
    **/
    private static $_commands = array(
            'import-blog-posts' => 'Import Posts From a Wordpress Blog',
            'import-comments' => 'Import Comments From a Wordpress Blog',
            'import-projects' => 'Import Projects From Wordpress',
            'import-rss:' => 'Import External RSS Feed',
            'import-posterous' => 'Import posts and comments from Posterous',
            'import-files' => 'Import posts from Files',
            'import-google' => 'Import posts and comments from Google+',
            'check-links:' => 'Check Links in Posts. Need substr for Domaincheck',
            'fix-postdates' => 'Fix the post_date field in sqlite',
            'post-email' => 'Post to blog by Email',
            'help' => 'This Help',
            'dry-run' => 'Don\'t apply',
            'force' => 'Force Overwrites',
    );
        
    /**
    * Print help
    *
    * @return void
    **/
    private static function _help()
    {
        print "Usage: {$_SERVER['argv'][0]} [OPTIONS]\n";
        foreach (self::$_commands as $h => $t) {
            printf("\t--%-16s\t%s\n", $h, $t);
        }
    }

    /**
    * Parse CLI Args
    *
    * @return void
    **/
    public static function parseArgs()
    {
        $options = getopt('h', array_keys(self::$_commands));
        
        $class = false;
        $value = null;
        $dryrun = false;
        $force = false;
        
        foreach ($options as $k => $v) {
            switch ($k) {
            case 'h':
            case 'help':
            default:
                self::_help();
                exit;
            case 'import-blog-posts':
                $class = 'Wp_Import_Blog_Posts';
                break;
            case 'import-comments':
                $class = 'Wp_Import_Comments';
                break;
            case 'import-projects':
                $class = 'Wp_Import_Projects';
                break;
            case 'post-email':
                $class = 'Post_By_Mail';
                break;
            case 'import-posterous':
                $class = 'Import_Posterous';
                break;
            case 'import-google':
                $class = 'Import_Google';
                break;
            case 'import-files':
                $class = 'Import_File';
                break;
            case 'import-rss':
                $class = 'Import_Rss';
                $value = $v;
                break;
            case 'check-links':
                self::checkLinks($v);
                exit;
            case 'fix-postdates':
                self::fixPostDates();
                exit;
            case 'dry-run':
                $dryrun = true;
                break;
            case 'force':
                $force = true;
                break;
            }
        }
        
        if ($class !== false) {
            include_once __DIR__ . '/importers/' . $class . ".php";
            $ret = new $class();
            $ret->setup($value, $dryrun, $force)->run();
            exit;
        } else {
            self::_help();
            exit;
        }
    }


    /**
    * Fix Post Dates in sqlite
    *
    * @return void
    **/
    public static function fixPostDates()
    {
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->find_many();
            
        foreach ($posts as $post) {

            print $post->post_date . " -> ";
            
            $parsed = strtotime($post->post_date);
            
            if ($parsed === false) {
                print "Can't Parse!\n";
                continue;
            }
            
            print date('c', $parsed) . "\n";
            
            if ($post->post_date == date('c', $parsed)) {
                continue;
            }
            
            $post->post_date = date('c', $parsed);
            
            $post->save();
        }

    }

    /**
    * Check Links in articles for validity
    *
    * @param string $substr String that needs to be in the url to check
    *
    * @return void
    **/
    public static function checkLinks($substr)
    {
        $posts = ORM::for_table('posts')
            ->order_by_desc('post_date')
            ->find_many();
            
        foreach ($posts as $post) {
        
            $match = preg_match_all(
                '#(href|src)=["\'](.*?)["\']#i', 
                $post->post_content,
                $matches
            );
            if (!$match) {
                continue;
            }
            
            foreach ($matches[2] as $url) {
                if (stristr($url, $substr) === false) {
                    d("Skipping: " . $url);
                    continue;
                }
            
                d("Checking Article: " . $post->post_slug . ' ID: ' . $post->ID);
                $ch = curl_init();
                curl_setopt(
                    $ch, 
                    CURLOPT_URL, 
                    $url
                );
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

                $ret = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                
                if ($info['http_code'] != 200) {
                    d("!! Failed for URL: " . $info['url']);
                }
            }
        }
    }

}
