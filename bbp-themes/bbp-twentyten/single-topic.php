<?php

/**
 * Single Topic
 *
 * @package bbPress
 * @subpackage Theme
 */

?>

<?php get_header(); ?>

		<div id="container">
			<div id="content" role="main">

				<?php do_action( 'bbp_template_notices' ); ?>

				<?php if ( bbp_is_forum_public( bbp_get_topic_forum_id(), false ) || current_user_can( 'read_private_forums' ) ) : ?>

					<?php while ( have_posts() ) : the_post(); ?>

						<div id="bbp-topic-wrapper-<?php bbp_topic_id(); ?>" class="bbp-topic-wrapper">
							<h1 class="entry-title"><?php bbp_title_breadcrumb(); ?></h1>
							<div class="entry-content">

								<?php bbp_topic_tag_list(); ?>

								<?php bbp_single_topic_description(); ?>

								<div id="ajax-response"></div>

								<?php if ( bbp_show_lead_topic() ) : ?>

									<table class="bbp-topic" id="bbp-topic-<?php bbp_topic_id(); ?>">
										<thead>
											<tr>
												<th class="bbp-topic-author"><?php _e( 'Creator', 'bbpress' ); ?></th>
												<th class="bbp-topic-content">

													<?php _e( 'Topic', 'bbpress' ); ?>

													<?php bbp_user_subscribe_link(); ?>

													<?php bbp_user_favorites_link(); ?>

												</th>
											</tr>
										</thead>

										<tfoot>
											<tr>
												<td colspan="2">

													<?php bbp_topic_admin_links(); ?>

												</td>
											</tr>
										</tfoot>

										<tbody>

											<tr class="bbp-topic-header">
												<td class="bbp-topic-author"><?php bbp_topic_author_link( array( 'type' => 'name' ) ); ?></td>

												<td class="bbp-topic-content">
													<a href="#bbp-topic-<?php bbp_topic_id(); ?>" title="<?php bbp_topic_title(); ?>">#</a>

													<?php printf( __( 'Posted on %1$s at %2$s', 'bbpress' ), get_the_date(), esc_attr( get_the_time() ) ); ?>

												</td>
											</tr>

											<tr id="post-<?php bbp_topic_id(); ?>" <?php post_class( 'bbp-forum-topic' ); ?>>

												<td class="bbp-topic-author"><?php bbp_topic_author_link( array( 'type' => 'avatar' ) ); ?></td>

												<td class="bbp-topic-content">

													<?php bbp_topic_content(); ?>

												</td>

											</tr><!-- #post-<?php bbp_topic_id(); ?> -->

										</tbody>
									</table><!-- #bbp-topic-<?php bbp_topic_id(); ?> -->

								<?php endif; ?>

								<?php get_template_part( 'bbpress/loop', 'replies' ); ?>

								<?php get_template_part( 'bbpress/form', 'reply' ); ?>

							</div>
						</div><!-- #bbp-topic-wrapper-<?php bbp_topic_id(); ?> -->

					<?php endwhile; ?>

				<?php else : ?>

					<div id="forum-private" class="bbp-forum-info">
						<h1 class="entry-title"><?php _e( 'Private', 'bbpress' ); ?></h1>
						<div class="entry-content">

							<div class="bbp-template-notice info">
								<p><?php _e( 'You do not have permission to view this forum.', 'bbpress' ); ?></p>
							</div>

						</div>
					</div><!-- #forum-private -->

				<?php endif; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
