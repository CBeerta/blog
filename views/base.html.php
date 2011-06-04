<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Claus Beerta - <?php echo $title; ?></title>
  <meta name="description" content="Claus Beerta">
  <meta name="author" content="Claus Beerta">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="/public/favicon.png">

  <link rel="stylesheet" href="/public/css/style.compressed.css?v=2">
  <link rel="stylesheet" href="/public/js/libs/fancybox/jquery.fancybox.compressed-1.3.4.css">

  <script src="/public/js/libs/modernizr-1.7.min.js"></script>
  <link href="/blog/feed" rel="alternate" title="Claus Beerta - Feed" type="application/atom+xml" />
</head>

<body>

  <div id="container">
    <header>
        <div id="logo">
          <h1><a href="<?php echo url_for(); ?>">Claus Beerta</a></h1>
          <h2>Stuff i do, don't and other babble.</h2>
        </div>
        <a href="http://github.com/CBeerta">
            <img style="position: absolute; top: 0; right: 0; border: 0;" src="https://d3nwyuy0nl342s.cloudfront.net/img/7afbc8b248c68eb468279e8c17986ad46549fb71/687474703a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub">
        </a>
    </header>
    <!-- end div#header -->
    <div id="menu">
    <ul>
<?php foreach($menu_items as $k => $v): ?>
        <li <?php echo ($active == $k) ? "class=\"active\"" : ""; ?>>
            <a href="<?php echo url_for($k); ?>"><?php echo $v; ?></a>
        </li>
<?php endforeach; ?>
    </ul>
    </div>
    <!-- end div#menu -->
    <div id="splash">
    <img src="/public/header-images/<?php echo randomHeaderImage('header-images/'); ?>" width="940" height="255" alt="" />
    </div>
    <div id="main" role="main">
        <div id="content">
<?php echo $content; ?>
        </div>
    <sidebar>
        <ul>
          <li id="search">
            <h2>Search</h2>
            <form method="POST" action="<?php echo url_for('sidebar', 'search'); ?>">
              <fieldset>
                <input type="text" id="search-text" name="s" placeholder="<?php echo isset($search) ? $search : 'Search'; ?>"/>
                <input type="submit" id="search-submit" value="Search" />
              </fieldset>
            </form>
          </li>
          <li id="social">
            <a href="<?php echo url_for('blog', 'feed'); ?>"><img title="RSS Feed" src="/public/img/social/rss_32.png"></a>
            <a href="mailto:claus@beerta.de"><img title="Email Me" src="/public/img/social/email_32.png"></a>
            <a href="http://www.flickr.com/photos/cbeerta"><img title="Flickr Page" src="/public/img/social/flickr_32.png"></a>
            <a href="http://amg.deviantart.com/"><img title="DeviantART" src="/public/img/social/deviantart_32.png"></a>
            <a href="https://github.com/CBeerta"><img title="Github" src="/public/img/social/github_32.png"></a>
          </li>
          <li>
<?php if (isset($sidebar)) echo $sidebar; ?>
            <h2>Github Projects</h2>
            <ul class="github">
              <!--li><a href="#">Eget tempor eget nonummy</a></li-->
            </ul>
            <h2>Flickr Fotostream</h2>
            <!-- Start of Flickr Badge --> 
            <div id="flickr_badge_uber_wrapper"> 
              <div id="flickr_badge_wrapper"> 
                <script type="text/javascript" src="http://www.flickr.com/badge_code_v2.gne?count=5&display=latest&size=t&layout=x&source=user&user=46080991%40N07"></script> 
              </div> 
            </div> 
            <!-- End of Flickr Badge -->             
            <h2>DeviantART Favourites</h2>
            <ul class="deviantart">
            </ul>
          </li>
        </ul>
    </sidebar>
    </div>
    <div class="clearfix"></div>
    <footer>
    <p>Copyright &copy; 2000 - <?php echo date('Y'); ?> Claus Beerta. All Rights Reserved. </p>
    <p id="links"><a href="/contact">Impressum</a></p>
    </footer>
  </div> <!-- eo #container -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.js"></script>
  <script>window.jQuery || document.write("<script src='/public/js/libs/jquery-1.5.1.min.js'>\x3C/script>")</script>
<?php if (isEditor()): // Only need these for editing purposes?>
  <script src="/public/js/libs/jquery.jeditable.js"></script>
  <script src="/public/js/libs/jquery.jeditable.autogrow.js"></script>
  <script src="/public/js/libs/jquery.autogrow.js"></script>
<?php endif; ?>
  <script src="/public/js/libs/fancybox/jquery.fancybox-1.3.4.pack.js"></script>

  <!-- scripts concatenated and minified via ant build script-->
  <script src="/public/js/plugins.js"></script>
  <script src="/public/js/script.js"></script>
  <!-- end scripts-->

  <!--[if lt IE 7 ]>
    <script src="/js/libs/dd_belatedpng.js"></script>
    <script>DD_belatedPNG.fix("img, .png_bg");</script>
  <![endif]-->

</body>
</html>
