<?php bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php bb_option('uri'); ?>"><?php bb_option('name'); ?></a> &raquo; <?php echo $profile_page_title; ?></h3>
<h2><?php echo get_user_name( $user->ID ); ?></h2>

<?php $self(); ?>

<?php bb_get_footer(); ?>
