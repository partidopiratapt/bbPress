<?php if ( !bb_is_topic() ) : ?>
<p id="post-form-title-container">
	<label for="topic"><?php _e( 'Title' ); ?>
		<input name="topic" type="text" id="topic" size="50" maxlength="100" tabindex="1" />
	</label>
</p>
<?php endif; do_action( 'post_form_pre_post' ); ?>

<p id="post-form-post-container">
	<label for="post_content"><?php _e( 'Post' ); ?>
		<textarea name="post_content" cols="50" rows="8" id="post_content" tabindex="5"></textarea>
	</label>
</p>
<p id="post-form-tags-container">
	<label for="tags-input"><?php _e( 'Tags (comma separated)' ); ?>
		<input id="tags-input" name="tags" type="text" size="50" maxlength="100" value="<?php bb_tag_name(); ?>" tabindex="6" />
	</label>
</p>
<?php if ( bb_is_tag() || bb_is_front() ) : ?>
<p id="post-form-forum-container">
	<label for="forum-id"><?php _e( 'Forum' ); ?>
		<?php bb_new_topic_forum_dropdown( 'tab=7' ); ?>
	</label>
</p>
<?php endif; ?>

<?php if ( bb_is_user_logged_in() && bb_is_subscriptions_active() ) : ?>
<p id="post-form-subscription-container" class="left">
	<?php bb_user_subscribe_checkbox( 'tab=8' ); ?>
</p>
<?php endif; ?>

<p id="post-form-submit-container" class="submit">
	<input type="submit" id="postformsub" name="Submit" value="<?php esc_attr_e( 'Send Post &raquo;' ); ?>" tabindex="9" />
</p>

<div class="clear"></div>

<p id="post-form-allowed-container" class="allowed"><?php _e( 'Allowed markup:' ); ?> <code><?php allowed_markup(); ?></code>. <br /><?php _e( 'You can also put code in between backtick ( <code>`</code> ) characters.' ); ?></p>
