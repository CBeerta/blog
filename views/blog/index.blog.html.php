    <h2 class="title">
        <?php if ($post->post_status != 'publish') echo 'Draft: '; ?>
        <a href="<?php echo url_for('blog', $post->post_slug); ?>">
            <?php echo $post->post_title; ?>
        </a>
    </h2>
    <p class="date">Posted: <?php echo formatDate($post->post_date); ?></p>
    <div class="entry" id="<?php echo $post->ID; ?>">
        <p><?php echo formatContent($post->post_content); ?></p>
<?php if (!empty($post->original_source)): ?>
        <a class="meta" href="<?php echo $post->original_source; ?>">Article Source</a>        
<?php endif; ?>
    </div>
