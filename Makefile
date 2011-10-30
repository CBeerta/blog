SOURCES = index.php controllers/*.php lib/*.php controllers/*/*.php
OPTION ?= --dry-run

all: csstidy phpcs

imports:
	php index.php --import-files
	#php index.php --import-rss "http://api.flickr.com/services/feeds/photos_public.gne?id=46080991@N07&lang=en-us&format=rss_200" $(OPTION)
	php index.php --import-rss "http://picasaweb.google.com/data/feed/base/user/106832871642761506709/albumid/5656649800328939665?alt=rss&hl=en_US" $(OPTION)
	php index.php --import-rss "http://backend.deviantart.com/rss.xml?q=gallery%3Aamg%2F23117831&type=deviation" $(OPTION)
	#php index.php --import-posterous $(OPTION)
	php index.php --import-google $(OPTION)


csstidy:
	csstidy public/css/style.css --silent=true | tr -d '\n' > public/css/style.compressed.css
	csstidy public/js/libs/fancybox/jquery.fancybox-1.3.4.css --silent=true | tr -d '\n' > public/js/libs/fancybox/jquery.fancybox.compressed-1.3.4.css

ci:
	#git svn dcommit
	git push

phpcs:
	phpcs $(SOURCES)
    


# vim: set tabstop=4 shiftwidth=4 noexpandtab:
