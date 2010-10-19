<?php
/**
 * bbPress Single Topic
 *
 * @package bbPress
 * @subpackage Template
 */
?>

<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<div id="topic-<?php bbp_topic_id(); ?>" class="bbp-topic-info">
					<h1 class="entry-title"><?php bbp_topic_title(); ?></h1>
					<div class="entry-content">

						<?php the_content(); ?>

					</div>
				</div><!-- #topic-<?php bbp_topic_id(); ?> -->

				<table id="topic-<?php bbp_topic_id(); ?>">
					<thead>
						<th><?php _e( 'Author', 'bbpress' ); ?></th>
						<th><?php _e( 'Content', 'bbpress' ); ?></th>
					</thead>

					<tfoot>
						<td colspan="2">&nbsp;<?php // @todo - Moderation links ?></td>
					</tfoot>

					<tbody>

						<?php while ( have_posts() ) : the_post(); ?>

							<tr id="reply-<?php bbp_topic_id(); ?>" <?php post_class( 'forum_topic' ); ?>>

								<td class="bbp-topic-author">
									<?php
										// @todo - abstract
										printf (
											'<a href="%1$s" title="%2$s">%3$s</a>',
											get_author_posts_url( get_the_author_meta( 'ID' ) ),
											sprintf( __( 'Posts by %s' ), esc_attr( get_the_author_meta( 'display_name' ) ) ),
											get_avatar( get_the_author_meta( 'ID' ), 40 )
										);
									?>
									<br />
									<?php
										// @todo - abstract
										printf(
											'<a href="%1$s" title="%2$s" class="url">%3$s</a>',
											get_author_posts_url( get_the_author_meta( 'ID' ) ),
											sprintf( __( 'Posts by %s' ), esc_attr( get_the_author_meta( 'display_name' ) ) ),
											get_the_author()
										);
									?>
								</td>

								<td class="bbp-topic-content">

									<?php the_content(); ?>

									<div class="entry-meta">

										<?php
											// @todo - abstract
											printf( __( 'Posted at %2$s on %3$s', 'bbpress' ),
												'meta-prep meta-prep-author',
												esc_attr( get_the_time() ),
												get_the_date()
											);
										?>

									</div>
								</td>

							</tr><!-- #topic-<?php bbp_topic_id(); ?> -->

						<?php endwhile; ?>

						<?php get_template_part( 'loop', 'bbp_replies' ); ?>

					</tbody>
				</table>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>