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
* Wordpress Blog Post Importer
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class Import_Posterous extends Importer
{
    /**
    * Import Posterous posts
    *
    * @return void
    **/
    public function run()
    {
        $value = $this->value;
        $dryrun = $this->dryrun;
        $force = $this->force;
        
        if (!isset($_SERVER['USER']) || !isset($_SERVER['PASSWORD'])) {
            d("You will have to set the 'USER' and 'PASSWORD' env variables");
            return;
        }

        $ret = self::posterousApi('users/me/sites/primary/posts/public', false);

        if (!$ret) {
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
            $post->post_content = $src->body_full;
            $post->guid = $src->slug . '-'. $src->id;
            $post->original_source = 'http://cbeerta.posterous.com/' . $src->slug;
            $post->post_status = 'draft';
            
            //d($post);
            if (!$dryrun) {
                $post->save();
                d("Imported: {$post->post_title}");
            } else {
                d("Would Import: {$post->post_title}");
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

}
