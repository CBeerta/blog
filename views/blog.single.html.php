<?php set('active', 'blog'); ?>
<?php if (empty($post)): ?>
    <h2>Post does not exist!</h2>
<?php endif; ?>
<div class="post" id="<?php echo $post->ID; ?>">
    <h2 class="title">
        <a href="<?php echo url_for('blog', $post->post_slug); ?>"><?php echo $post->post_title; ?></a>
    </h2>
    <p class="byline">Posted by Claus</p>
    <p class="date">Posted: <?php echo formatDate($post->post_date); ?></p>
    <div class="entry <?php echo isEditor() ? 'editor editable' : ''; ?>" id="<?php echo $post->ID; ?>">
        <p><?php echo $post->post_content; ?></p>
    </div>
    <div class="clearfix"></div>
</div>
