SOURCES=index.php controllers/*.php lib/*.php

all: phpcs

ci:
	git svn dcommit
	git push playground homebrew

phpcs:
	phpcs $(SOURCES)
    


# vim: set tabstop=4 shiftwidth=4 noexpandtab:
