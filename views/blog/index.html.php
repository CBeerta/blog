<?php set('active', 'blog'); ?>
<?php if (empty($posts)): ?>
<alert>No more Posts!</alert>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
<div class="post" id="<?php echo $post->ID; ?>">
    <h2 class="title">
        <?php if ($post->post_status != 'publish') echo 'Draft: '; ?>
        <a href="<?php echo url_for('blog', $post->post_slug); ?>">
            <?php echo $post->post_title; ?>
        </a>
    </h2>
    <!--p class="byline">Posted by Claus</p-->
    <p class="date">Posted: <?php echo formatDate($post->post_date); ?></p>
    <div class="entry" id="<?php echo $post->ID; ?>">
        <p><?php echo formatContent($post->post_content); ?></p>
<?php if (!empty($post->original_source)): ?>
        <a class="meta" href="<?php echo $post->original_source; ?>">Article Source</a>        
<?php endif; ?>    
    </div>
    <div class="clearfix"></div>
</div>
<?php endforeach; ?>
<div class="pagination">
    <h2 class="prev">&lt;<a href="<?php echo url_for('blog', 'pager', $offset + $ppp); ?>">Older Entries</a></h2>
<?php if ($offset >= $ppp): ?>
    <h2 class="next"><a href="<?php echo url_for('blog', 'pager', $offset - $ppp); ?>">Newer Entries</a>&gt;</h2>
<?php endif; ?>
    <h2 class="archive"><a href="<?php echo url_for('blog', 'archive'); ?>">Archive</a></h2>
</div>
