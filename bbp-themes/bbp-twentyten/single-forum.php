<?php

/**
 * Single Forum
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<?php do_action( 'bbp_template_notices' ); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php if ( bbp_is_forum_public( bbp_get_forum_id(), false ) || current_user_can( 'read_private_forums' ) ) : ?>

						<div id="forum-<?php bbp_forum_id(); ?>" class="bbp-forum-info">
							<h1 class="entry-title"><?php bbp_title_breadcrumb(); ?></h1>
							<div class="entry-content">

								<?php the_content(); ?>

								<?php bbp_single_forum_description(); ?>

								<?php if ( bbp_get_forum_subforum_count() && bbp_has_forums() ) : ?>

									<?php get_template_part( 'bbpress/loop', 'forums' ); ?>

								<?php endif; ?>

								<?php if ( !bbp_is_forum_category() && bbp_has_topics() ) : ?>

									<?php get_template_part( 'bbpress/pagination', 'topics' ); ?>

									<?php get_template_part( 'bbpress/loop',       'topics' ); ?>

									<?php get_template_part( 'bbpress/pagination', 'topics' ); ?>

									<?php get_template_part( 'bbpress/form',       'topic'  ); ?>

								<?php elseif( !bbp_is_forum_category() ) : ?>

									<?php get_template_part( 'bbpress/no',         'topics' ); ?>

									<?php get_template_part( 'bbpress/form',       'topic'  ); ?>

								<?php endif; ?>

							</div>
						</div><!-- #forum-<?php bbp_forum_id(); ?> -->

					<?php else : // Forum exists, user no access ?>

						<?php get_template_part( 'bbpress/no', 'access' ); ?>

					<?php endif; ?>

				<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
