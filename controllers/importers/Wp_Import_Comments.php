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
* Wordpress Blog Post Importer
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Wp_Import_Comments extends Importer
{
    /**
    * Import Comments from Wordpress.
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
        mysql_set_charset('utf8', $db);
        
        $res = mysql_query(
            "SELECT * 
             FROM wp_comments,wp_posts 
             WHERE wp_comments.comment_post_ID=wp_posts.ID"
        );
        
        /*
        ORM::for_table('comments')->raw_query('DELETE FROM comments')->find_one(27);
        */
        
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

}

