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

/**
* Helpers
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Helpers
{

    /**
    * Options with defaults, overridable in config.ini
    **/
    protected static $options = array (
        'cache_dir' => '/var/tmp/',
        'dbfile' => './data/planner.db',
        'projects_dir' => './data/projects',
        'docs_dir' => './data/docs',
        'posts_dir' => './data/posts',
        'public_url' => 'http://localhost/data/uploads',
        'public_loc' => './data/uploads',
        'deviantart_items' => 4,
        'posts_per_page' => 10,
        'date_format' => 'M d Y',
        'base_uri' => '/',
        'public_dir' => './public/',
        'google_id' => null,
        'google_api_key' => null,
    );
    
    /**
    * not Found Magic
    *
    * @return void
    **/
    public static function notFound()
    {
        $app = Slim::getInstance();
        
        $parts = explode('/', $app->request()->getResourceUri());
        
        /**
        * First look through all projects and see if theres a match
        **/
        $slugs = array();
        $projects = Projects::loadProjects();
        
        foreach ($projects as $proj) {
            $slugs[strtolower($proj->post_slug)] = strtolower($proj->post_slug);
        }
        
        foreach ($parts as $part) {
            if (in_array(strtolower($part), $slugs)) {
                $app->redirect('/projects/' . $slugs[strtolower($part)]);
            }
        }
        
        /**
        * Then look at all blog posts for a match
        **/
        $posts = ORM::for_table('posts')->find_many();
        
        $slugs = array();
        foreach ($posts as $post) {
            $slugs[] = $post->post_slug;
        }

        foreach ($parts as $part) {
            if (in_array($part, $slugs)) {
                $app->redirect('/blog/' . $part);
            }
        }
        
        /**
        * Finally, render our 404 because nothing was found
        **/
        $app->render('404.html');
    }

    /**
    * Options Store
    *
    * @param string $key   Key
    * @param mixed  $value Value
    *
    * @return void
    **/
    public static function option($key, $value = null)
    {
        if ($value == null) {
            return self::$options[$key];
        }
        
        self::$options[$key] = $value;
    }

    /**
    * Plural a word
    *
    * @param int    $count Number of objects
    * @param string $word  Word
    *
    * FIXME: This is retarded. Should use I18N Twig extension
    *
    * @return string
    **/
    public static function plural($count, $word)
    {
        switch ($count) {
        case 0:
            return "no " . $word . "s";
        case 1:
            return "one " . $word;
        default:
            return $count . " " . $word . "s";
        }
    }

    /**
    * Return a Random Header Image
    *
    * @param string $image_dir Directory under $public_dir with images
    *
    * @return void
    **/
    public static function randomHeaderImage($image_dir)
    {
        $dir = Helpers::option('public_dir') . '/' .  $image_dir;
        $glob = "{{$dir}*.jpg, {$dir}*.png}";
        
        $files = array();
        foreach (glob($glob, GLOB_BRACE) as $filename) {
            $files[] = $filename;
        }

        mt_srand((double)microtime()*1000000); // seed for PHP < 4.2
        $rand = mt_rand(0, count($files) - 1); // $i was incremented as we went along

        return basename($files[$rand]);
    }

    /**
    * Format a list of Tags
    *
    * @param string $tags String with comma seperated tags
    *
    * @return formatted date
    **/
    public static function formatTags($tags)
    {
        $tags = explode(',', $tags);
        $ret = '';

        foreach ($tags as $tag) {
            $ret .= '<a href="/posts/tag/' . self::buildSlug($tag) . '">';
            $ret .= $tag;
            $ret .= '</a>, ';
        }
        $ret = rtrim($ret, ', ');
    
        return $ret;
    }

    /**
    * Add Tags to a Post
    *
    * @param array $tags   Array with  tags
    * @param int   $postID postid to add tags to
    *
    * @return void
    **/
    public static function addTags($tags, $postID)
    {
        foreach ($tags as $tag) {
            $tag = trim($tag);

            $name = ucfirst(strtolower($tag));
            $slug = Helpers::buildSlug($tag);
            
            if (empty($slug)) {
                continue;
            }
            
            $term = ORM::for_table('post_terms')
                ->raw_query(
                    "
                    INSERT OR IGNORE INTO `post_terms`
                    (`name`,`slug`) 
                    VALUES
                    ('{$name}', '{$slug}');
                    ", array()
                )->count();
                
            $term = ORM::for_table('post_terms')
                ->where('slug', $slug)
                ->find_one();                       

            $term = ORM::for_table('term_relations')
                ->raw_query(
                    "
                    INSERT OR IGNORE INTO `term_relations`
                    (`posts_ID`,`post_terms_ID`) 
                    VALUES
                    ({$postID}, {$term->ID});
                    ", array()
                )->count();
        }
    }

    /**
    * Comment Image Url Formatter for Twig
    *
    * @param string $uri URl or EMAIL for the image
    *
    * @return string 
    **/
    public static function commentImage($uri)
    {
        $ret = '';
        
        if (strpos($uri, '@') !== false) {
            // email, use gravatar
            $ret = 'http://www.gravatar.com/avatar/';
            $ret .= md5($uri);
            $ret .= '?d=retro&s=32';
        } else if (strpos($uri, 'google') !== false) {
            // google url, probably from G+ comments
            $ret = $uri;
            $ret .= '?sz=32';
        } 
        
        return $ret;
    }
     
    
    /**
    * Format a DateTime / String for display
    *
    * @param string $date Date to Format
    *
    * @return formatted date
    **/
    public static function formatDate($date)
    {
        $date = new DateTime($date);
        
        if ( !$date ) {
            return $date;
        }
        
        return $date->format(option('date_format'));
    }

    /**
    * Format content
    *
    * @param string $content    The content to format
    * @param string $line_break What Line Break to use
    *
    * @return html
    **/
    public static function formatContent($content, $line_break='<br />')
    {
        if (strstr($content, '<') !== false) {
            /**
            * This is somewhat specific. I dunno if Wordpress generated these.
            * It SUCKS. maybe easier to just clean my posts?
            *
            * Anyhow: Any line ending that is NOT a html tag followed by 2 linebreaks
            * will be converted to two '<br>' tags
            *
            * This seems to convert my posts best, leaving properly formatted 
            * ones intact and only alter the ones that need it.
            *
            * FIXME: This has to DIAF! Fix the goddamn content 
            *       in the database already!
            **/
            $content = preg_replace(
                '#([\w:;\.,!\?\(\)]+?)(\r|\n){2,}#', 
                '\1<br><br>', 
                $content
            );
        } else {
            // This is probably Markdown or plaintext
            $content = Markdown($content);
        }
        
        $pattern = '#\s*\<pre\s(.*?)\>(.*?)\</pre\>\s*#si';
        $content = preg_replace_callback(
            $pattern, 
            'self::geshiHighlight',
            $content
        );
        

        return $content;
    }

    /**
    * Format <pre> tags with Geshi
    *
    * @param string $content The content to format
    *
    * @return html
    **/
    public static function geshiHighlight($content)
    {
        $line = null;
        $lang = null;
        
        $code = $content[2];

        preg_match_all("#\s*(.*?)=\"(.*?)\"#", $content[1], $matches);

        foreach ($matches[1] as $k=>$v) {
            switch ($v)
            {
            case 'lang':
                $lang = $matches[2][$k];
                break;
            case 'lineno':
                $line = $matches[2][$k];
                break;
            default:    
                break;
            }
        }
        
        if ($lang !== null) {

            include_once "/usr/share/php-geshi/geshi.php";
        
            $geshi = new GeSHi($code, $lang);
            
            $geshi->set_tab_width(2);
            $geshi->set_overall_class("vibrant {$lang} codecolorer");
            $geshi->set_header_type(GESHI_HEADER_DIV);
            $geshi->enable_classes();
            $geshi->enable_keyword_links(false);
            $geshi->set_overall_style('white-space:nowrap');

            if ($line !== null) {
                $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
                $geshi->start_line_numbers_at($line); 
            }

            return $geshi->parse_code();
        }
    
        return $code;
    }

    /**
    * Create a "Slug" from a title
    *
    * @param string $title   The title to create a slug from
    * @param string $sep     A Seperator
    * @param string $charset The Charset to use
    *
    * @return string a slug
    **/
    public static function buildSlug($title, $sep = "-", $charset = "UTF-8")
    {
        // Build Slug
        $slug = strtolower(htmlentities($title, ENT_COMPAT, $charset));
        $slug = preg_replace(
            '/&(.)(acute|cedil|circ|lig|grave|ring|tilde|uml);/', "$1", 
            $slug
        );
        $slug = preg_replace(
            '/([^a-z0-9]+)/', 
            $sep, 
            html_entity_decode($slug, ENT_COMPAT, $charset)
        );
        $slug = trim($slug, $sep);
        
        return $slug;
    }

    /**
    * Check if the Client is allowed to edit
    *
    * @TODO This should obviously be a bit fancier
    *
    * @return void
    **/
    public static function isEditor()
    {
        if (php_uname('n') === 'phoebe' /*&& PHP_SAPI == 'cli'*/) {
            // for now only allow editing on phoebe
            return true;
        }
        if (php_uname('n') === 'alkaia' /*&& PHP_SAPI == 'cli'*/) {
            // for now only allow editing on phoebe
            return true;
        }
        
        return false;
    }

}

