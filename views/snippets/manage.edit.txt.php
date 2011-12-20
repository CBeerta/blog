#-- post_title: <?php echo $post->post_title; ?> 
<?php if (is_numeric($post->ID)): ?>
#-- tags: <?php echo $post->tag_names; ?> 
<?php endif; ?>
#-- post_status: <?php echo $post->post_status; ?> 
#-- post_date: <?php echo $post->post_date->format('r'); ?> 
#-- original_source: <?php echo $post->original_source; ?> 
#-- post_slug: <?php echo $post->post_slug; ?> 
#-- protected: <?php echo $post->protected; ?> 
#-- post_type: <?php echo $post->post_type; ?> 

<?php echo $post->post_content; ?>

