<?php
/**
*  PHP Template Engine.
* 
*  Source: https://github.com/thinkphp/php-template-engine
*
*  Implements the same interface as Savant3 and Smarty, but is more lightweight.
*  It's is originally created in the Sitepoint article: 
*  http://www.sitepoint.com/article/beyond-template-engine
*
*  Usage:
*  <?php
*    $tpl = new Template('path/to/templates');
*    $tpl->assign('variable','some value');
*    $tpl->display('template');
*  ?>
*
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
* Simple Tempalte Class
*
* @category Template_Engine
* @package  MyWebPage
* @author   Claus Beerta <claus@beerta.de>
* @license  http://www.opensource.org/licenses/mit-license.php MIT License
* @link     http://claus.beerta.de/
**/
class SimpleTemplate
{
 
    public $vars;


    public $path;

    /**
    * Constructor of class
    * 
    * Sets the path to the template files. 
    *   
    * @param String $path => path to template files.
    *
    * @return void
    */
    public function __construct($path=null) 
    {
        $this->path = $path;
        $this->vars = array();   
    }  

    /**
    * Sets a template variable
    *   
    * @param String $name  the name of the variable template to set.
    * @param String $value the value of the variable template. 
    *
    * @return void
    */
    public function set($name, $value) 
    {
        $this->vars[$name] = $value;
    }

    /**
    * Sets the path to the template files.
    *   
    * @param String $path => path to template files.
    *
    * @return void.
    */
    public function setPath($path) 
    {
        $this->path = $path;
    }

    /**
    * Open, parse, and return the template file.
    *   
    * @param String $file the template filename.
    *
    * @return String
    */
    public function fetch($file) 
    {
        //extract the vars to local namespace.
        extract($this->vars);

        //start output buffering.
        ob_start();

        //include the file
        include $this->path . $file;

        //get the contents of the buffer.
        $contents = ob_get_contents();

        //end buffering and discard.
        ob_end_clean();

        //return output String
        return($contents);  
    }

    /**
    * Display the template directly.
    *   
    * @param String $file the template filename.
    *
    * @return (String)
    */
    public function display($file) 
    {
        echo $this->fetch($file); 
    }


}   

