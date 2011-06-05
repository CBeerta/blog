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

if ( PHP_SAPI != 'cli' ) {
    // dont do anything if we're not a cli php
    return;
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
    * Parse CLI Args
    *
    * @return void
    **/
    public static function parseArgs()
    {
        $commands = array(
            'import-blog-posts' => 'Import Posts From a Wordpress Blog',
            'import-comments' => 'Import Comments From a Wordpress Blog',
            'import-projects' => 'Import Projects From Wordpress',
            'import-rss:' => 'Import External RSS Feed',
            'import-posterous' => 'Import posts and comments from Posterous',
            'check-links:' => 'Check Links in Posts. Need substr for Domaincheck',
            'post-email' => 'Post to blog by Email',
            'help' => 'This Help',
            'dry-run' => 'Don\'t apply',
            'force' => 'Force Overwrites',
        );
        
        $options = getopt('h', array_keys($commands));
        
        // This may be used by configure, just set it to something
        $_SERVER['HTTP_HOST'] = 'Not Available';
        // Pull user config
        call_if_exists('configure');
        
        $command = false;
        $value = null;
        $dryrun = false;
        $force = false;
        
        foreach ($options as $k => $v) {
            switch ($k) {
            case 'h':
            case 'help':
                print "Usage: {$_SERVER['argv'][0]} [OPTIONS]\n";
                foreach ($commands as $h => $t) {
                    printf("\t--%-16s\t%s\n", $h, $t);
                }
                $command = false;
                break;
            case 'import-blog-posts':
                $command = 'importBlogPosts';
                break;
            case 'import-comments':
                $command = 'importComments';
                break;
            case 'import-projects':
                $command = 'importProjects';
                break;
            case 'post-email':
                $command = 'postByMail';
                break;
            case 'import-posterous':
                $command = 'importPosterous';
                break;
            case 'import-rss':
                $command = 'importRSS';
                $value = $v;
                break;
            case 'check-links':
                $command = 'checkLinks';
                $value = $v;
                break;
            case 'dry-run':
                $dryrun = true;
                break;
            case 'force':
                $force = true;
                break;
            }
        }
        
        if ($command !== false) {
            self::$command($value, $dryrun, $force);
            exit;
        }
    }

    /**
    * Import from posterous
    *
    * @param string $value  Not used here
    * @param boot   $dryrun Dryrun
    * @param boot   $force  Force Overwrites
    *
    * @return void
    **/
    public static function importPosterous($value, $dryrun, $force)
    {
        
        if (!isset($_SERVER['USER']) || !isset($_SERVER['PASSWORD'])) {
            d("You will have to set the 'USER' and 'PASSWORD' env variables");
            return;
        }

        $ret = self::posterousApi('users/me/sites/primary/posts/public', false);

        if ($ret === false) {
            d("Could not get Posts. Check Auth?");
            return;
        }
        
        foreach ($ret as $src) {
        
            $post = ORM::for_table('posts')
                ->where('post_slug', $src->slug)
                ->find_one();
                
            if (!$post) {
                $post = ORM::for_table('posts')->create();
            } else if (!$force) {
                d("Skipping already imported: {$src->slug}. Use --force to Update");
                continue;
            }
            
            $post->post_date = $src->display_date;
            $post->post_slug = $src->slug;
            $post->post_title = $src->title;
            $post->post_content = $src->body_html;
            $post->guid = $src->slug . '-'. $src->id;
            $post->post_status = 'draft';
            
            if (!$dryrun) {
                $post->save();
            }
            
            if (!empty($ret->comments)) {
                d("Holy Shit, COMMENTS! Need to import these.");
            }
        }

    }

    /**
    * Do requests against posterous API
    *
    * @param string $url        What Command to query
    * @param bool   $need_token Does this request require a token?
    *
    * @return void
    **/
    public static function posterousApi($url, $need_token=true)
    {

        if ($need_token && !self::$_posterous_api_token) {
            $token = self::posterousApi('auth/token', false);
            if (!$token) {
                return false;
            }
            self::$_posterous_api_token = $token->api_token;
        }

        $ch = curl_init();
        curl_setopt(
            $ch, 
            CURLOPT_URL, 
            'http://posterous.com/api/2/' . $url
        );

        if ($need_token) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt(
                $ch, 
                CURLOPT_POSTFIELDS, 
                "&api_token=" . self::$_posterous_api_token
            );
        }

        curl_setopt(
            $ch, 
            CURLOPT_USERPWD, 
            $_SERVER['USER'] . ':' . $_SERVER['PASSWORD']
        ); 
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        $ret = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($info['http_code'] != 200) {
            return false;
        }        

        return json_decode($ret);
    }
    

    /**
    * Create a new blog post by Email
    *
    * @param string $value  Not used here
    * @param boot   $dryrun Dryrun
    * @param boot   $force  Force Overwrites
    *
    * @return void
    **/
    public static function postByMail($value, $dryrun, $force)
    {
        if (ftell(STDIN) !== 0) {
            fwrite(STDERR, "Nothing on STDIN.\n");
            return;
        }
        
        $content = '';
        $input = fopen('php://stdin', 'r');
        
        $headers = array();
        $body_opened = false;
        
        while ($line = fgets($input)) {
            
            if (preg_match("#^(\S+):\s?(.*)\Z#i", $line, $matches)) {
                // Pull the Headers
                $headers[strtolower($matches[1])] = $matches[2];
            } else if (preg_match("#^\Z#", $line) && count($headers) > 0) {
                // Newline without anything, and some headers seen
                // this must be the body
                $body_opened = true;
            } else if ($body_opened && preg_match("#^--\s\Z#", $line)) {
                // --\s on a single line marks the signature. close body
                $body_opened = false;
            } else if ($body_opened) {
                $content .= $line;
            } else {
                // ignore anything else that is not header or body
            }

        }
        
        if (!isset($headers['subject']) || empty($content)) {
            d("Can't deal with this mail");
            return;
        }

        if ($headers['delivered-to'] !== 'claus@aello.beerta.net'
            || $headers['return-path'] !== '<claus@beerta.net>'
        ) {
            d("Source not verified, not posting");
            return;
        }
        
        $post = ORM::for_table('posts')
            ->where('post_title', $headers['subject'])
            ->find_one();
            
        if (!$post) {
            $post = ORM::for_table('posts')->create();
        }
        
        $post->post_date = date('c');
        $post->post_slug = buildSlug($headers['subject']);
        $post->post_title = $headers['subject'];
        $post->post_content = trim($content);
        $post->guid = $post->post_slug . '-' . mktime();
        $post->post_status = 'draft';
        
        if (!$dryrun) {
            $post->save();
        }
        
        d($headers);

        //d($post);
    }
    /**
    * Check Links in articles for validity
    *
    * @param string $substr String that needs to be in the url to check
    * @param boot   $dryrun Dryrun
    * @param boot   $force  Force Overwrites
    *
    * @return void
    **/
    public static function checkLinks($substr, $dryrun, $force)
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

    /**
    * Import An External RSS into our content
    *
    * @param string $feed_uri Url to the feed to import
    * @param boot   $dryrun   Dryrun
    * @param boot   $force    Force Overwrites
    *
    * @return void
    *
    * FIXME: Gee, HTML pastery action. I should shoot myself.
    **/
    public static function importRSS($feed_uri, $dryrun, $force)
    {
        d("Will import {$feed_uri}");
        
        $rss = new SimplePie();        
        
        $rss->set_feed_url($feed_uri);
        $rss->set_cache_location('/var/tmp');
        $rss->set_cache_duration(43200);
        $rss->init();
        $rss->handle_content_type();
        
        // don't sort by pubdate, 
        $rss->enable_order_by_date(false); 
        
        $items = array();
        foreach ( $rss->get_items() as $item ) {
            $post = ORM::for_table('posts')
                ->where_like('post_title', $item->get_title())
                ->order_by_desc('post_date')
                ->find_one();

            if (isset($post->ID) && $force === false) {
                d("Skipping: " . $post->post_title);
                continue;
            }
            
            d("Importing: " . $item->get_title());
            
            if ($force!== false && isset($post->ID)) {
                $new = ORM::for_table('posts')->find_one($post->ID);
            } else {
                $new = ORM::for_table('posts')->create();
            }
            
            $content  = '<div class="rss-imported">';
            
            if (isset($item->get_enclosure()->thumbnails[0])) {
                // FIXME this might be somewhat DeviantART Specific
                $content .= '<a href="' . $item->get_enclosure()->link . '">';
                $content .= '<img src="' . $item->get_enclosure()->thumbnails[0];
                $content .= '"></a>';
            }
            
            if (isset($item->get_enclosure()->description)) {
                $content .= $item->get_enclosure()->description;
            } else {
                $content .= $item->get_content();
            }
            
            $content .= '<br /><a class="meta" href="' . $item->get_link() . '">';
            $content .= 'Article Source</a>';
            $content .= '</div>';
            
            $new->post_status = 'draft';
            $new->post_title = $item->get_title();
            $new->post_date = $item->get_date('c');
            $new->post_content = $content;
            $new->post_slug = basename(strtolower($item->get_id()));
            $new->guid = basename(strtolower($item->get_id()));
            
            if (!$dryrun) {
                $new->save();
            }
            
            d($new->as_array());
        }
    }

    /**
    * Import Comments from Wordpress.
    *
    * @param boot $value  Not Uses here
    * @param boot $dryrun Dryrun
    * @param boot $force  Force Overwrites
    *
    * @return void
    **/
    public static function importComments($value, $dryrun, $force)
    {
        $dbhost = 'aello.local';
        $dbname = 'claus';
        $dbuser = $_SERVER['DBUSER'];
        $dbpass = $_SERVER['DBPASS'];
        
        $db = mysql_connect($dbhost, $dbuser, $dbpass);
        mysql_select_db($dbname, $db);
        mysql_set_charset('utf8', $db);
        
        $res = mysql_query(
            "SELECT * 
             FROM wp_comments,wp_posts 
             WHERE wp_comments.comment_post_ID=wp_posts.ID"
        );
        
        ORM::for_table('comments')->raw_query('DELETE FROM comments')->find_one(27);
        while ($data = mysql_fetch_assoc($res)) {
            
            // Find a Post with mathing title
            $post = ORM::for_table('posts')
                ->where_like('post_title', $data['post_title'])
                ->find_one();
                
            if (!isset($post->ID)) {
                d("No Matching Post found for {$data['post_title']}");
                continue;
            }
            d("Importing Comments for: " . $post->post_title);
                
            $comment = ORM::for_table('comments')->create();
            
            $comment->post_ID = $post->ID;
            $comment->comment_author = $data['comment_author'];
            $comment->comment_author_email = $data['comment_author_email'];
            $comment->comment_author_url = $data['comment_author_url'];
            $comment->comment_date = $data['comment_date'];
            $comment->comment_content = iconv(
                'UTF-8', 
                'ISO-8859-1//TRANSLIT//IGNORE', 
                $data['comment_content']
            );
            $comment->comment_status = ($data['comment_approved'] == 1) 
                ? 'visible' 
                : 'hidden';
            
            if (!$dryrun) {
                $comment->save();
            }
            
            d($comment);
        }
        
        return;
    }
        
    
    /**
    * Import Blog Posts from Wordpress.
    *
    * @param boot $value  Not Uses here
    * @param boot $dryrun Dryrun
    * @param boot $force  Force Overwrites
    *
    * @return void
    **/
    public static function importBlogPosts($value, $dryrun, $force)
    {
        include_once __DIR__.'/projects.php';

        $dbhost = 'aello.local';
        $dbname = 'claus';
        $dbuser = $_SERVER['DBUSER'];
        $dbpass = $_SERVER['DBPASS'];
        
        $slugs = array();
        
        $projects = Projects::loadProjects();
        
        foreach ($projects as $post) {
            $slugs[] = $post->post_slug;
        }
        d($slugs);
        
        
        $db = mysql_connect($dbhost, $dbuser, $dbpass);
        mysql_select_db($dbname, $db);
        mysql_set_charset('utf8', $db);
        
        $res = mysql_query(
            "
            SELECT *
            FROM `wp_posts`
            WHERE 
                post_type='post' AND
                post_status='publish'
            "
        );
        
        ORM::for_table('posts')->raw_query('DELETE FROM posts')->find_one(27);
        while ($data = mysql_fetch_assoc($res)) {
            if (in_array($data['post_name'], $slugs)) {
                // Skip projects
                continue;
            }
            $post = ORM::for_table('posts')->create();
            
            $post->post_date = $data['post_date'];
            $post->post_slug = $data['post_name'];
            $post->post_title = $data['post_title'];
            $post->post_content = iconv(
                'UTF-8', 
                'ISO-8859-1//TRANSLIT//IGNORE', 
                $data['post_content']
            );
            $post->post_content = str_replace(
                'claus.beerta.de/blog/wp-content/plugins/wp-o-matic/cache', 
                'idisk.beerta.net/public/wp-o-matic-cache',
                $post->post_content
            );
            $post->guid = $data['guid'];
            $post->post_status = $data['post_status'];
            
            if (!$dryrun) {
                $post->save();
            }
            d($post);
        }
    }


    /**
    * Import Projects from Wordpress.
    *
    * @param boot $value  Not Uses here
    * @param boot $dryrun Dryrun
    * @param boot $force  Force Overwrites
    *
    * @return void
    **/
    public static function importProjects($value, $dryrun, $force)
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
                
            if (file_exists($filename) && $force !== true) {
                d("Not overwriting {$filename}");
                continue;
            }

            if (!$dryrun) {
                file_put_contents(
                    $filename, 
                    utf8_encode($data['post_title'] . "\n\n" . $content)
                );
            }
        }
    }

}
