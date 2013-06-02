OPTION ?= --dry-run
SOURCES = setup.php \
	 controllers/*.php \
	 lib/helpers.php \
	 lib/resize.php \
	 lib/TwigView.php \
	 controllers/*/*.php \
	 public/index.php

all: css phpcs lint

imports:
	./import $(OPTION) --import-files
	#
	./import --post-type=deviantart $(OPTION) --import-rss="http://backend.deviantart.com/rss.xml?q=gallery%3Aamg%2F23117831&type=deviation"
	#
	./import --post-type=photo $(OPTION) --import-rss="http://picasaweb.google.com/data/feed/base/user/106832871642761506709/albumid/5656649800328939665?alt=rss&hl=en_US&imgmax=d"
	#
	# ./import --post-type=photo $(OPTION) --import-rss "http://picasaweb.google.com/data/feed/base/user/106832871642761506709/albumid/5666564519291602657?alt=rss&hl=en_US&imgmax=d"
	#
	./import $(OPTION) --import-google

	# ./import $(OPTION) --import-rss="https://github.com/CBeerta.atom" --post-type=activity
	# ./import $(OPTION) --import-rss="http://backend.deviantart.com/rss.xml?q=favby%3Aamg+sort%3Atime&type=deviation" --post-type activity

css:
	# csstidy public/js/libs/fancybox/jquery.fancybox-1.3.4.css --silent=true | tr -d '\n' > public/js/libs/fancybox/jquery.fancybox.compressed-1.3.4.css
	# csstidy vendor/Skeleton/stylesheets/base.css --silent=true | tr -d '\n' > public/css/base.css
	# csstidy vendor/Skeleton/stylesheets/skeleton.css --silent=true | tr -d '\n' > public/css/skeleton.css
	cp vendor/Skeleton/stylesheets/base.css public/css/base.css
	cp vendor/Skeleton/stylesheets/skeleton.css public/css/skeleton.css

lint:
	for source in $(SOURCES) ; do php -l $$source || exit 1 ; done

ci:
	git push

phpcs:
	phpcs $(SOURCES)

unittests:
	rm -rf data/
	mkdir -vp data/projects data/docs data/posts data/uploads
	cat schema.sql | sqlite3 data/planner.db
	cat tests/testdata.sql | sqlite3 data/planner.db
	cp tests/2010-12-29\ webtail-a-tail-for-files-located-on-a-webserver.html data/projects
	cp tests/Help.page data/docs
	phpunit -v
    
build: phpcs lint unittests

# vim: set tabstop=4 shiftwidth=4 noexpandtab:
