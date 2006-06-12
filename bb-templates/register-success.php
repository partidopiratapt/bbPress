<?php bb_get_header(); ?>

<h3><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <?php _e('Register'); ?></h3>

<h2><?php _e('Great!'); ?></h2>

<p>Your registration as <strong><?php echo $user_login; ?></strong> was successful. Within a few minutes you should receive an email with your password.</p>

<?php bb_get_footer(); ?>
