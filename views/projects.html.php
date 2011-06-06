<?php if (empty($projects)): ?>
    <h2>Project does not exist!</h2>
<?php return; endif; ?>
<?php foreach ($projects as $proj): ?>
<div class="post">
    <h2 class="title"><a href="<?php echo url_for('projects', $proj->post_slug); ?>"><?php echo $proj->post_title; ?></a></h2>
    <!--p class="byline">Posted by Claus</p-->
    <p class="date">Posted: <?php echo $proj->post_date->format(option('date_format')); ?></p>
    <div class="entry">
        <p>
            <?php echo $proj->teaser; ?>
        </p>
<?php if ($body): ?>
        <p>
            <?php echo $proj->content; ?>
        </p>
<?php elseif (!empty($proj->content)): ?>
    <div class="meta">
        <p class="links"><a href="<?php echo url_for('projects', $proj->post_slug); ?>" class="comments">Continue Reading</a></p>
    </div>
<?php endif; ?>
    </div>
    <!--div class="meta">
        <p class="links"><a href="#" class="comments"></a></p>
    </div-->
    <div class="clearfix"></div>
</div>
<?php endforeach; ?>
