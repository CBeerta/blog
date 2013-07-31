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

use Slim\Slim;
use Michelf\Markdown;

/**
* TwigView
*
* The TwigView is a custom View class that renders templates using the Twig
* template language (http://www.twig-project.org/).
*
* @category Personal_Website
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class TwigView extends \Slim\View
{
    /**
     * @var array The options for the Twig environment, see
     * http://www.twig-project.org/book/03-Twig-for-Developers
     */
    public static $twigOptions = array();

    /**
     * @var TwigEnvironment The Twig environment for rendering templates.
     */
    private $_twigEnvironment = null;

    /**
     * Render Twig Template
     *
     * This method will output the rendered template content
     *
     * @param string $template The path to the Twig template
     *
     * @return void
     */
    public function render( $template )
    {
        $env = $this->_getEnvironment();
        $template = $env->loadTemplate($template);
        return $template->render($this->data);
    }
    
    /**
     * Creates new TwigEnvironment if it doesn't already exist, and returns it.
     *
     * @return TwigEnvironment
     */
    private function _getEnvironment() 
    {
        if ( !$this->_twigEnvironment ) {
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem($this->getTemplatesDirectory());
            $this->_twigEnvironment = new Twig_Environment(
                $loader,
                self::$twigOptions
            );

            $this->_twigEnvironment->addFilter(
                'format_content',
                new Twig_Filter_Function('TwigView::formatContent')
            );
            $this->_twigEnvironment->addFilter(
                'plural',
                new Twig_Filter_Function('TwigView::plural')
            );
            $this->_twigEnvironment->addFilter(
                'comment_image',
                new Twig_Filter_Function('TwigView::commentImage')
            );
            $this->_twigEnvironment->addFilter(
                'format_tags',
                new Twig_Filter_Function('TwigView::formatTags')
            );
        }
        return $this->_twigEnvironment;
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
        if (preg_match('#\<(br|p)(\s|/)?>#i', $content)) {
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
            $content = Markdown::defaultTransform($content);
        }
        
        $pattern = '#\s*\<pre\s(.*?)\>(.*?)\</pre\>\s*#si';
        $content = preg_replace_callback(
            $pattern, 
            'Helpers::geshiHighlight',
            $content
        );
        

        return $content;
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
        } 
        
        return $ret;
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
        
            if ($tag == '') {
                continue;
            }
            
            $ret .= '<a href="/blog/tag/' . Helpers::buildSlug($tag) . '">';
            $ret .= $tag;
            $ret .= '</a> ';
        }
    
        return $ret;
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



}

