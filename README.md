# My Personal WebLog

Decided to roll my own. Just because i can. That's why!

This is my Personal Fun Project. It may act as inspiration. Don't expect it to work for you. (wheter it inspires you, or scare you away is really up to you)

This ain't Wordpress. It's as bare bones as it gets. It may kill your kittens too.

# Uses

* Limonade : https://github.com/CBeerta/limonade
* PHP-Markdown : http://michelf.com/projects/php-markdown
* Idiorm : https://github.com/j4mie/idiorm
* Simplepie : https://github.com/simplepie/simplepie

# Requirements

* PHP 5.3
* PDO SQLite
* Curl

# Installation

* Drop Contents into root directory. It does not support being served out of a subdirectory.
* Make sure `.htaccess` works, or your webserver redirecty all requests to `index.php`
* Download all modules in `vendor/`. Look at .gitmodules for details.
* Adapt config.ini (Check index.php configure section for all available options)
* Create Posts Database

# Todo

* Photography page. If et all
* Some way to actually create new Posts would be neat. Nothing like creating new posts with `sqlite3` on the Console though



