<?php
/**
 * Template Name: Booki Single Booking View
 * This is used to display a single booking item when an item in a booking-list grid is clicked.
 */
 
$_Booki_BookingViewTmpl = new Booki_BookingViewTmpl();

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
				<div>
					<?php Booki_ThemeHelper::includeTemplate('master.php') ?>
				</div>
				<div>
				<?php 
					$render = new Booki_Render();
					echo $render->bookingList($_Booki_BookingViewTmpl->projectListArgs);
				?>
				</div>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();
