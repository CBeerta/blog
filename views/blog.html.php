<?php set('active', 'blog'); ?>
<?php if (empty($posts)): ?>
    <h2>Post does not exist!</h2>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
<div class="post" id="<?php echo $post->ID; ?>">
    <h2 class="title">
        <a href="<?php echo url_for('blog', $post->post_slug); ?>"><?php echo $post->post_title; ?></a>
    </h2>
    <p class="byline">Posted by Claus</p>
    <p class="date">Posted: <?php echo formatDate($post->post_date); ?></p>
    <div class="entry <?php echo isEditor() ? 'editor editable' : ''; ?>" id="<?php echo $post->ID; ?>">
        <p><?php echo $post->post_content; ?></p>
    </div>
    <?php if (isEditor()): ?>
    <div class="clearfix"></div>
    <div class="editor">
        <img class="editor trash_post" id="<?php echo $post->ID; ?>" onclick="trash_post(<?php echo $post->ID; ?>);" src="/public/img/trash_stroke_32x32.png">
        <img class="editor toggle_publish" id="<?php echo $post->ID; ?>" onclick="toggle_publish(<?php echo $post->ID; ?>);" src="/public/img/<?php echo ($post->post_status != 'publish') ? 'denied' : 'check_alt'; ?>_32x32.png">
    </div>
    <?php endif; ?>
    <div class="clearfix"></div>
</div>
<?php endforeach; ?>
