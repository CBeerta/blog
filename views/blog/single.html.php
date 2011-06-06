<?php if (empty($post)): ?>
<alert>Post does not exist!</alert>
<?php return; endif; ?>
<div class="post" id="<?php echo $post->ID; ?>">
<?php include __DIR__ . '/index.' . $post->post_type . '.html.php'; ?>
    <div class="clearfix"></div>
<?php if (isEditor()): ?>
    <div class="editor">
        <img class="editor trash_post" id="<?php echo $post->ID; ?>" onclick="trash_post(<?php echo $post->ID; ?>);" src="/public/img/trash_stroke_32x32.png">
        <img class="editor toggle_publish" id="<?php echo $post->ID; ?>" onclick="toggle_publish(<?php echo $post->ID; ?>);" src="/public/img/<?php echo ($post->post_status != 'publish') ? 'denied' : 'check_alt'; ?>_32x32.png">
    </div>
    <div class="clearfix"></div>
<?php endif; ?>
</div>
<?php foreach ($comments as $c): ?>
<div class="comment" id="<?php echo $c->ID; ?>">
    <img class="gravatar" src="http://www.gravatar.com/avatar/<?php echo md5($c->comment_author_email); ?>?d=retro&s=64" />
    <div class="comment_content">
        <div class="title">
            <a href="<?php echo $c->comment_author_url; ?>"><?php echo $c->comment_author; ?></a>
            &nbsp;|&nbsp;
            <small><?php echo formatDate($c->comment_date); ?></small>
        </div>
        <p>
            <?php echo formatContent($c->comment_content); ?>
        </p>
    </div>
    <div class="clearfix"></div>
</div>
<?php endforeach; ?>
