<?php
	/**
	* Template Name: Booki Cart Details
	*/
	$_Booki_CartTmpl = new Booki_CartTmpl();
	
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
				<?php Booki_ThemeHelper::includeTemplate('checkoutgrid.php') ?>
			</div>
		</div><!-- #content -->
	</div><!-- #primary -->
	<?php get_sidebar( 'content' ); ?>
</div><!-- #main-content -->

<?php
get_sidebar();
get_footer();