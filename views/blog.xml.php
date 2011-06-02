<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	>

<channel>
	<title>Claus Beerta</title>
	<atom:link href="http://claus.beerta.net/blog/feed/" rel="self" type="application/rss+xml" />
	<link>http://claus.beerta.net/blog</link>
	<description>Stuff i do, don&#039;t and other babble.</description>
	<lastBuildDate><?php echo $build_date; ?></lastBuildDate>

	<language>en</language>
	<sy:updatePeriod>hourly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<generator>http://claus.beerta.net/blog/</generator>
	
<?php foreach ($posts as $p): ?>
    <item>
		<title><?php echo $p->post_title; ?></title>
		<link><?php echo option('host') . url_for('blog', $p->post_slug); ?></link>
		<pubDate>Tue, 04 Aug 2009 07:35:22 +0000</pubDate>
		<dc:creator>Claus Beerta</dc:creator>

		<guid isPermaLink="false"><?php echo option('host') . url_for('blog', $p->post_slug); ?></guid>
		<description><![CDATA[<?php echo strip_tags($p->post_content); ?>]]></description>
		<content:encoded><![CDATA[<?php echo $p->post_content; ?>]]></content:encoded>
	</item>	
<?php endforeach; ?>
</channel>
</rss>
