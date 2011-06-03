SOURCES=index.php controllers/*.php lib/*.php

all: phpcs

phpcs:
	phpcs $(SOURCES)
    


# vim: set tabstop=4 shiftwidth=4 noexpandtab:
