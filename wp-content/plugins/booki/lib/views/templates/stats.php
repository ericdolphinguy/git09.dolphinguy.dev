<?php
/**
 * Template Name: Booki Stats template
 * This is used to display the users stats on a separate WordPress front end page 
 * instead of the default which is to display stats in dashboard.
 */
 
get_header(); ?>

<div id="main-content" class="main-content">
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
			<?php
				// Start the Loop.
				while ( have_posts() ) : the_post();

					// Include the page content template.
					get_template_part( 'content', 'page' );
					
				endwhile;
			?>
			<?php Booki_ThemeHelper::includeTemplate('user/singlecolstatspartial.php') ?>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();