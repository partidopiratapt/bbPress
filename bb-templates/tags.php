<?php bb_get_header(); ?>

<?php login_form(); ?>

<h2><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <?php _e('Tags'); ?></h2>

<p><?php _e('This is a collection of tags that are currently popular on the forums.'); ?></p>

<?php tag_heat_map( 9, 38, 'pt', 80 ); ?>

<?php bb_get_footer(); ?>
