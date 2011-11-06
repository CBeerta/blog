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

/**
* Blog, Stuff that is only accessible by isEditor()
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class BlogEdit
{
    /**
    * Load a Post, return as json
    *
    * @return json
    **/
    public static function loadJSON()
    {
        $app = Slim::getInstance();
        
        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;
            
        $post = ORM::for_table('posts')->find_one($id);
        
        $content = '# ' . $post->post_title . "\n\n" . $post->post_content;

        return $app->response()->body($content);
    }

    /**
    * Save a Post, return html
    *
    * @return json
    **/
    public static function save()
    {
        $app = Slim::getInstance();
        
        if (Helpers::isEditor() !== true) {
            return $app->response()->body('No Permission to edit!');
        }
        
        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;
        $value = isset($_POST['value']) 
            ? $_POST['value'] 
            : null;
            
        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) || is_null($value) ) {
            return $app->response()->body('Will not Save!');
        }
        
        $title_match = "|^#\s?(.*?)\n|";
        if (preg_match($title_match, $value, $matches)) {
            $value = trim(preg_replace($title_match, '', $value));
            $post->post_title = trim($matches[1]);
        }

        $post->post_content = $value;
        $post->save();
        
        return $app->response()->body(Helpers::formatContent($value));
    }

    /**
    * Save Tags
    *
    * @return json
    **/
    public static function saveTags()
    {
        $app = Slim::getInstance();
        
        if (Helpers::isEditor() !== true) {
            return $app->response()->body('No Permission to edit!');
        }
        
        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;
        $value = isset($_POST['value']) 
            ? $_POST['value'] 
            : null;

        if (is_null($id) || is_null($value)) {
            return $app->response()->body('POST Data incomplete!');
        }        
        
        /* Remove all existing relations for this Post */
        $relations = ORM::for_table('term_relations')
            ->where_equal('posts_ID', $id)
            ->delete_many();

        foreach (preg_split('#[\s,]#', $value) as $t) {
        
            /* Find if tag exists */
            $tag = ORM::for_table('post_terms')
                ->where('slug', Helpers::buildSlug($t))
                ->find_one();
                
            if (!$tag) {
                /* If not, create it */
                $tag = ORM::for_table('post_terms')->create();
                $tag->name = $t;
                $tag->slug = Helpers::buildSlug($t);
                $tag->save();
            }
            
            /* And insert new relations back to db */            
            $rel = ORM::for_table('term_relations')->create();
            $rel->posts_ID = $id;
            $rel->post_terms_ID = $tag->ID;
            $rel->save();
        }
        
        return $app->response()->body($value);
    }
    /**
    * Trash a Post
    *
    * @return partial
    **/
    public static function trash()
    {
        $app = Slim::getInstance();
        
        if (Helpers::isEditor() !== true) {
            return $app->response()->body('No Permission to edit!');
        }

        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) ) {
            return $app->response()->body('Will not Save!');
        }
        
        $post->delete();

        return $app->response()->body('Deleted!');
    }

    /**
    * Toggle the Publish status
    *
    * @return partial
    **/
    public static function togglePublish()
    {
        $app = Slim::getInstance();
        
        if (Helpers::isEditor() !== true) {
            return $app->response()->body('No Permission to edit!');
        }

        $id = ( isset($_POST['id']) && is_numeric($_POST['id']) ) 
            ? $_POST['id'] 
            : null;

        $post = ORM::for_table('posts')->find_one($id);
        
        if ( !$post || is_null($id) ) {
            return $app->response()->body('Will not Save!');
        }
        
        $post->post_status = ($post->post_status == 'publish' )
            ? 'draft'
            : 'publish';
        
        $post->save();
        
        return $app->response()->body($post->post_status);
    }

}

