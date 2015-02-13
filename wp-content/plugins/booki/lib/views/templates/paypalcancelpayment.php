<?php
/**
 * Template Name: Booki Paypal payment cancelled.
 *
 * This is the page displayed when a client tries to make payment through paypal but cancels, instead of continuing with payment.
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
			<div class="booki">
				<?php Booki_ThemeHelper::includeTemplate('paypalcancelpaymentpartial.php') ?>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();