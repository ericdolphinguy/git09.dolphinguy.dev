<?php
/**
 * Template Name: Booki History template
 * This is used to display the users history on a separate WordPress front end page 
 * instead of the default which is to display history in the dashboard.
 */

get_header(); ?>

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<div class="entry-content">
				<?php
					// Start the Loop.
					while ( have_posts() ) : the_post();

						// Include the page content template.
						get_template_part( 'content', 'page' );
						
					endwhile;
				?>
				<div class="booki">
					<?php Booki_ThemeHelper::includeTemplate('user/historypartial.php') ?>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
