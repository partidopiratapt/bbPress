<?php bb_get_header(); ?>

<h3><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <?php _e('Register'); ?></h3>

<h2><?php _e('Registration'); ?></h2>

<?php if ( !$bb_current_user ) : ?>
<form method="post" action="<?php option('uri'); ?>register.php">
<fieldset>
<legend><?php _e('Profile Information'); ?></legend>
<p>A password will be mailed to the email address you provide. Make sure to whitelist our domain (<?php echo $bb->domain; ?>) so the confirmation email doesn't get caught by any  filters. </p>
<table width="100%">
<?php if ( $user_safe === false ) : ?>
<tr class="error">
<th scope="row"><?php _e('Username:'); ?></th>
<td><input name="user_login" type="text" id="user_login" size="30" maxlength="30" /><br />
<?php _e('Your username was not valid, please try again'); ?></td>
</tr>
<?php else : ?>
<tr class="required">
<th scope="row"><?php _e('Username<sup>*</sup>:'); ?></th>
<td><input name="user_login" type="text" id="user_login" size="30" maxlength="30" value="<?php if (1 != $user_login) echo $user_login; ?>" /></td>
</tr>
<?php endif; ?>
<?php if ( is_array($profile_info_keys) ) : foreach ( $profile_info_keys as $key => $label ) : ?>
<tr<?php if ( $label[0] ) { echo ' class="required"'; $label[1] .= '<sup>*</sup>'; } ?>>
  <th scope="row"><?php echo $label[1]; ?>:</th>
  <td><input name="<?php echo $key; ?>" type="text" id="<?php echo $key; ?>" size="30" maxlength="140" value="<?php echo $$key; ?>" /><?php
if ( $$key === false ) :
	if ( $key == 'user_email' )
		_e('<br />There was a problem with your email; please check it.');
	else
		_e('<br />The above field is required.');
endif;
?></td>
</tr>
<?php endforeach; endif; ?>
</table>
<p><sup>*</sup>These items are <span class="required">required</span>.</p>
</fieldset>

<?php bb_do_action('extra_profile_info', $user); ?>

<p class="submit">
  <input type="submit" name="Submit" value="<?php _e('Register'); ?> &raquo;" />
</p>
</form>
<?php else : ?>
<p><?php _e('You&#8217;re already logged in, why do you need to register?'); ?></p>
<?php endif; ?>

<?php bb_get_footer(); ?>