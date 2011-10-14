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
class TwigView extends Slim_View
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
            include_once __DIR__ . '/../vendor/Twig/lib/Twig/Autoloader.php';
            Twig_Autoloader::register();
            $loader = new Twig_Loader_Filesystem($this->getTemplatesDirectory());
            $this->_twigEnvironment = new Twig_Environment(
                $loader,
                self::$twigOptions
            );

            $this->_twigEnvironment->addFilter(
                'format_content',
                new Twig_Filter_Function('Helpers::formatContent')
            );
            $this->_twigEnvironment->addFilter(
                'md5',
                new Twig_Filter_Function('md5')
            );
            $this->_twigEnvironment->addFilter(
                'comment_image',
                new Twig_Filter_Function('Helpers::commentImage')
            );
            $this->_twigEnvironment->addFilter(
                'format_tags',
                new Twig_Filter_Function('Helpers::formatTags')
            );
        }
        return $this->_twigEnvironment;
    }

}

