# My Personal Website

[Look Here](http://claus.beerta.net/)

Decided to roll my own. Just because i can. That's why!

This is my Personal Fun Project. It may act as inspiration. Don't expect it to work for you.

This ain't Wordpress. It's as bare bones as it gets. It may kill your kittens too.

[![Build Status](https://secure.travis-ci.org/CBeerta/Playground.png?branch=retheme)](http://travis-ci.org/CBeerta/Playground)

# Uses

* [Slim](https://github.com/codeguy/Slim)
* [Twig](http://www.twig-project.org/)
* [PHP-Markdown](http://michelf.com/projects/php-markdown)
* [Idiorm](https://github.com/j4mie/idiorm)
* [Simplepie](https://github.com/simplepie/simplepie)
* [Cling](https://github.com/CBeerta/Cling)
* [Skeleton](https://github.com/CBeerta/Skeleton)

# Requirements

* PHP 5.3
* PDO SQLite
* Some Importers may require additional Stuff.

# Installation

* Drop Contents into root directory. It does not support being served out of a subdirectory.
* Make sure `.htaccess` works, or your webserver redirecty all requests to `index.php`
* Webservers DocRoot should be the `public/` folder.
* Download all modules in `vendor/`. Look at .gitmodules for details.
* Adapt config.ini (Check index.php configure section for all available options)
* Create Posts Database
