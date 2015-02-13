<?php
/**
 * Template Name: Booki Paypal process payment
 *
 * We just got redirected from paypal. It's now time to ask the user to confirm payment and credit their account.
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
			<?php Booki_ThemeHelper::includeTemplate('paypalprocesspaymentspartial.php') ?>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();