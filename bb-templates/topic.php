<?php get_header(); ?>

<?php login_form(); ?>

<h3><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <a href="<?php forum_link(); ?>"><?php forum_name(); ?></a></h3>
<h2><?php topic_title(); ?></h2>

<?php topic_tags(); ?>

<?php bb_do_action('under_title', ''); ?>
<?php if ($posts) : ?>
<div class="nav">
<?php topic_pages(); ?>
</div>
<ol id="thread" start="<?php echo $list_start; ?>">

<?php foreach ($posts as $post) : ?>
	<li id="post-<?php post_id(); ?>" <?php alt_class('post'); ?>>
	
		<div class="threadauthor">
			<p><strong><?php post_author_link(); ?></strong><br />
			  <small><?php post_author_type(); ?></small></p>
		</div>
		
		<div class="threadpost">
			<div class="post"><?php post_text(); ?></div>
			<div class="poststuff">Posted: <?php post_time(); ?> <a href="#post-<?php post_id(); ?>">#</a> <?php post_ip(); ?> <?php post_edit_link(); ?> <?php post_delete_link(); ?></div>
		</div>
	</li>
<?php endforeach; ?>

</ol>
<div class="clearit"><br style=" clear: both;" /></div>
<p><a href="<?php topic_rss_link(); ?>">RSS feed for this thread</a></p>
<div class="nav">
<?php topic_pages(); ?>
</div>
<?php endif; ?>
<?php if ( topic_is_open( $post->topic_id ) ) : ?>
<h2>Reply</h2>
<?php post_form(); ?>
<?php else : ?>
<h2>Topic Closed</h2>
<p>This topic has been closed to new replies.</p>
<?php endif; ?>
<div class="admin">
<?php topic_delete_link(); ?> <?php topic_close_link(); ?> <?php topic_sticky_link(); ?>
</div>
<?php get_footer(); ?>