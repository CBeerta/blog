<?php if (empty($posts)): ?>
<alert>No more Posts!</alert>
<?php return; endif; ?>
<?php foreach ($posts as $post): ?>
<div class="post" id="<?php echo $post->ID; ?>">
<?php include __DIR__ . '/index.' . $post->post_type . '.html.php'; ?>
    <div class="clearfix"></div>
</div>
<?php endforeach; ?>
<?php if (isset($ppp)): ?>
<div class="pagination">
    <h2 class="prev">&lt;<a href="<?php echo url_for('blog', 'pager', $offset + $ppp); ?>">Older Entries</a></h2>
<?php if ($offset >= $ppp): ?>
    <h2 class="next"><a href="<?php echo url_for('blog', 'pager', $offset - $ppp); ?>">Newer Entries</a>&gt;</h2>
<?php endif; ?>
    <h2 class="archive"><a href="<?php echo url_for('blog', 'archive'); ?>">Archive</a></h2>
</div>
<?php endif; ?>
