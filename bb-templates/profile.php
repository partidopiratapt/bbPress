<?php bb_get_header(); ?>

<h3 class="bbcrumb"><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; Profile</h3>
<h2 id="userlogin"><?php echo get_user_name( $user->ID ); ?></h2>

<?php if ( $updated ) : ?>
<div class="notice">
<p>Profile updated. <a href="<?php profile_tab_link( $user_id, 'edit' ); ?>">Edit again &raquo;</a></p>
</div>
<?php elseif ( $user_id == $bb_current_user->ID ) : ?>
<p>This is how your profile appears to a fellow logged in member, you may <a href="<?php profile_tab_link( $user_id, 'edit' ); ?>">edit this information</a>.
You can also <a href="<?php favorites_link(); ?>">manage your favorites</a> and subscribe to your favorites' <a href="<?php favorites_rss_link(); ?>"><abbr title="Really Simple Syndication">RSS</abbr> feed</a>.</p>
<?php endif; ?>

<?php bb_profile_data(); ?>

<h3 id="useractivity">User Activity</h3>

<div id="user-replies" class="user-recent"><h4>Recent Replies</h4>
<?php if ( $posts ) : ?>
<ol>
<?php foreach ($posts as $bb_post) : $topic = get_topic( $bb_post->topic_id ) ?>
<li<?php alt_class('replies'); ?>><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a> <?php if ( $user->ID == $bb_current_user->ID ) _e('You last replied'); else _e('User last replied'); ?>: <?php bb_post_time(); ?> ago.
<?php
if ( strtotime(bb_get_post_time()) < strtotime(get_topic_time()) ) {
	echo ' <span class="freshness">Most recent reply: ';
	topic_time();
	echo ' ago.</span>';
} else {
	echo ' <span class="freshness">No replies since.</span>';
}
?>	
</li>
<?php endforeach; ?>
</ol>
<?php else : if ( $page ) : ?>
<p>No more replies.</p>
<?php else : ?>
<p>No replies yet.</p>
<?php endif; endif; ?>
</div>

<div id="user-threads" class="user-recent">
<h4>Threads Started</h4>
<?php if ( $threads ) : ?>
<ol>
<?php foreach ($threads as $topic) : ?>
<li<?php alt_class('threads'); ?>><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a> Started: <?php topic_start_time(); ?> ago.
<?php
if ( strtotime(get_topic_start_time()) < strtotime(get_topic_time()) ) {
	echo ' <span class="freshness">Most recent reply: ';
	topic_time();
	echo ' ago.</span>';
} else {
	echo ' <span class="freshness">No replies.</span>';
}
?>	
</li>
<?php endforeach; ?>
</ol>
<?php else : if ( $page ) : ?>
<p>No more topics posted.</p>
<?php else : ?>
<p>No topics posted yet.</p>
<?php endif; endif;?>
</div><br style="clear: both;" />

<?php profile_pages(); ?>

<?php bb_get_footer(); ?>
