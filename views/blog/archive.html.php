<?php if (empty($posts)): ?>
<alert>Not a single Post!</alert>
<?php endif; ?>
<h4>Blog Archive</h4>
<div class="archive">
<?php foreach ($posts as $post): ?>
<h2>
    <a href="<?php echo url_for('blog', $post->post_slug); ?>"><?php echo $post->post_title; ?></a>
    <div class="date"><?php echo formatDate($post->post_date); ?></div>
</h2>
<?php endforeach; ?>
</div>
