<?php

/**
 * View Handler
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<?php do_action( 'bbp_template_notices' ); ?>

				<div id="bbp-view-<?php bbp_view_id(); ?>" class="bbp-view">
					<h1 class="entry-title"><?php bbp_view_title(); ?></h1>
					<div class="entry-content">

						<?php bbp_breadcrumb(); ?>

						<?php bbp_set_query_name( 'bbp_view' ); ?>

						<?php if ( bbp_view_query() ) : ?>

							<?php bbp_get_template_part( 'bbpress/pagination', 'topics'    ); ?>

							<?php bbp_get_template_part( 'bbpress/loop',       'topics'    ); ?>

							<?php bbp_get_template_part( 'bbpress/pagination', 'topics'    ); ?>

						<?php else : ?>

							<?php bbp_get_template_part( 'bbpress/feedback',   'no-topics' ); ?>

						<?php endif; ?>

						<?php bbp_reset_query_name(); ?>

					</div>
				</div><!-- #bbp-view-<?php bbp_view_id(); ?> -->

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
