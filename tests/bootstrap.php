<?php

use Slim\Slim;

require_once __DIR__ . '/../setup.php';

//Prepare mock HTTP request
$_SERVER['REDIRECT_STATUS'] = "200";
$_SERVER['HTTP_HOST'] = "slim";
$_SERVER['HTTP_CONNECTION'] = "keep-alive";
$_SERVER['HTTP_CACHE_CONTROL'] = "max-age=0";
$_SERVER['HTTP_ACCEPT'] = "application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
$_SERVER['HTTP_USER_AGENT'] = "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_4; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Chrome/6.0.472.63 Safari/534.3";
$_SERVER['HTTP_ACCEPT_ENCODING'] = "gzip,deflate,sdch";
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = "en-US,en;q=0.8";
$_SERVER['HTTP_ACCEPT_CHARSET'] = "ISO-8859-1,utf-8;q=0.7,*;q=0.3";
$_SERVER['HTTP_IF_MODIFIED_SINCE'] = "Sun, 03 Oct 2010 17:00:52 -0400";
$_SERVER['HTTP_IF_NONE_MATCH'] = '"abc123"';
$_SERVER['HTTP_COOKIE'] = 'foo=bar; foo2=bar2';
$_SERVER['PATH'] = "/usr/bin:/bin:/usr/sbin:/sbin";
$_SERVER['SERVER_SIGNATURE'] = "";
$_SERVER['SERVER_SOFTWARE'] = "Apache";
$_SERVER['SERVER_NAME'] = "slim";
$_SERVER['SERVER_ADDR'] = "127.0.0.1";
$_SERVER['SERVER_PORT'] = "80";
$_SERVER['REMOTE_ADDR'] = "127.0.0.1";
$_SERVER['DOCUMENT_ROOT'] = '/home/account/public';
$_SERVER['SERVER_ADMIN'] = "you@example.com";
$_SERVER['SCRIPT_FILENAME'] = __FILE__;
$_SERVER['REMOTE_PORT'] = "55426";
$_SERVER['REDIRECT_URL'] = "/";
$_SERVER['GATEWAY_INTERFACE'] = "CGI/1.1";
$_SERVER['SERVER_PROTOCOL'] = "HTTP/1.1";
$_SERVER['REQUEST_METHOD'] = "GET";
$_SERVER['QUERY_STRING'] = "";
$_SERVER['REQUEST_URI'] = "/";
$_SERVER['SCRIPT_NAME'] = '/bootstrap.php';
$_SERVER['PHP_SELF'] = '/bootstrap.php';
$_SERVER['REQUEST_TIME'] = "1285647051";
$_SERVER['argv'] = array();
$_SERVER['argc'] = 0;


//Start session before PHPUnit sends output. This only prevents us from using
//the default Slim Session cookie store.
session_start();

//Mock custom view
class CustomView extends \Slim\View {
    function render($template) { echo "Custom view"; }
}

$_SERVER['REQUEST_METHOD'] = "GET";
$_ENV['SLIM_MODE'] = null;
$_COOKIE['foo'] = 'bar';
$_COOKIE['foo2'] = 'bar2';
$_SERVER['REQUEST_URI'] = "/";

$app = new Slim(
    array(
        'view' => 'TwigView',
        /* 'view' => 'CustomView', */
        'templates.path' => Helpers::option('templates.path'),
        'mode' => 'testing',
    )
);

$app->configureMode(
    'production', function() use ($app) {
        $app->config(
            array(
            'log.enable' => false,
            'debug' => true
            )
        );
    }
);

