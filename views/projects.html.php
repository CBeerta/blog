<?php set('active', 'projects'); ?>
<?php if (empty($projects)): ?>
    <h2>Project does not exist!</h2>
<?php endif; ?>
<?php foreach ($projects as $proj): ?>
<?php #print_r($proj); ?>

<div class="post">
    <h2 class="title"><a href="<?php echo url_for('projects', $proj['slug']); ?>"><?php echo $proj['title']; ?></a></h2>
    <p class="byline">Posted by Claus</p>
    <div class="entry">
        <p>
            <?php echo $proj['teaser']; ?>
        </p>
<?php if ($body): ?>
        <p>
            <?php echo $proj['content']; ?>
        </p>
<?php elseif (!empty($proj['content'])): ?>
    <div class="meta">
        <p class="links"><a href="<?php echo url_for('projects', $proj['slug']); ?>" class="comments">Continue Reading</a></p>
    </div>
<?php endif; ?>
    </div>
    <!--div class="meta">
        <p class="links"><a href="#" class="comments"></a></p>
    </div-->
    <div class="clearfix"></div>
</div>
<?php endforeach; ?>
