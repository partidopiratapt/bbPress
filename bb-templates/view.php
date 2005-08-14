<?php get_header(); ?>

<?php login_form(); ?>

<h2><a href="<?php option('uri'); ?>"><?php option('name'); ?></a> &raquo; <?php view_name(); ?></h2>

<?php if ( isset($topics) || isset($stickies) ) : ?>

<table id="latest">
<tr>
	<th>Topic</th>
	<th>Posts</th>
	<th>Last Poster</th>
	<th>Freshness</th>
</tr>

<?php if ( isset($stickies) ) : foreach ( $stickies as $topic ) : ?>
<tr<?php alt_class('topic', 'sticky'); ?>>
	<td>Sticky: <big><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></big></td>
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php topic_last_poster(); ?></td>
	<td class="num"><small><?php topic_time(); ?></small></td>
</tr>
<?php endforeach; endif; ?>

<?php if ( isset($topics) ) : foreach ( $topics as $topic ) : ?>
<tr<?php alt_class('topic'); ?>>
	<td><a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></td>
	<td class="num"><?php topic_posts(); ?></td>
	<td class="num"><?php topic_last_poster(); ?></td>
	<td class="num"><small><?php topic_time(); ?></small></td>
</tr>
<?php endforeach; endif; ?>
</table>
<div class="nav">
</div>
<?php endif; ?>
<?php view_pages(); ?>

<?php get_footer(); ?>
