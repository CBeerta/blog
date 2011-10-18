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

/**
* Wordpress Projects Importer
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Wp_Import_Projects extends Importer
{
    /**
    * Import Projects from Wordpress.
    *
    * @return void
    **/
    public function run()
    {
        $value = $this->value;
        $dryrun = $this->dryrun;
        $force = $this->force;
        
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

                $content .= "<a href=\"".option('public_url') . $matches[0] . "\">";
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
