<?php

/**
 * Template Name: bbPress - Topic Index
 *
 * @package bbPress
 * @subpackage Themes
 */

?>

<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<?php do_action( 'bbp_template_notices' ); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<div id="topics-front" class="bbp-topics-front">
						<h1 class="entry-title"><?php bbp_title_breadcrumb(); ?></h1>
						<div class="entry-content">

							<?php the_content(); ?>

							<?php get_template_part( 'loop', 'bbp_topics' ); ?>

						</div>
					</div><!-- #topics-front -->

				<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
