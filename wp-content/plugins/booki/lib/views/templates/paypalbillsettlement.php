<?php
/**
 * Template Name: Booki Bill settlement
 *
 * This is the page displayed when a client tries to make payment through a 
	pay now direct link embeded in their invoice which is normally sent via email. More specifically, this is the payment method used
	when payments are enabled and the admin creates bookings manually through the control panel.
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
				<?php Booki_ThemeHelper::includeTemplate('paypalbillsettlementpartial.php') ?>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();