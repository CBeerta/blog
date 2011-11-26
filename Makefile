SOURCES = index.php controllers/*.php lib/*.php controllers/*/*.php
OPTION ?= --dry-run

all: csstidy phpcs

imports:
	php import.php --import-files $(OPTION)
	php import.php --import-rss "http://backend.deviantart.com/rss.xml?q=gallery%3Aamg%2F23117831&type=deviation" --post-type deviantart $(OPTION)
	php import.php --import-rss "http://picasaweb.google.com/data/feed/base/user/106832871642761506709/albumid/5656649800328939665?alt=rss&hl=en_US" --post-type photo $(OPTION)
	php import.php --import-rss "http://picasaweb.google.com/data/feed/base/user/106832871642761506709/albumid/5666564519291602657?alt=rss&hl=en_US" --post-type photo $(OPTION)
	php import.php --import-google $(OPTION)

	#php index.php --import-rss "https://github.com/CBeerta.atom" --post-type activity $(OPTION)
	#php index.php --import-rss "http://backend.deviantart.com/rss.xml?q=favby%3Aamg+sort%3Atime&type=deviation" --post-type activity $(OPTION)

csstidy:
	csstidy public/css/style.css --silent=true | tr -d '\n' > public/css/style.compressed.css
	csstidy public/js/libs/fancybox/jquery.fancybox-1.3.4.css --silent=true | tr -d '\n' > public/js/libs/fancybox/jquery.fancybox.compressed-1.3.4.css

ci:
	#git svn dcommit
	git push

phpcs:
	phpcs $(SOURCES)
    


# vim: set tabstop=4 shiftwidth=4 noexpandtab:
