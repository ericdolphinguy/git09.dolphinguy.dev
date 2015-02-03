<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Selvatica
 */
?>

	</div><!-- #content -->

	<footer id="colophon" class="site-footer row" role="contentinfo">
		<div class="site-info">
			<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'selvatica' ) ); ?>"><?php printf( __( 'Proudly powered by %s', 'selvatica' ), 'WordPress' ); ?></a>
			<span class="sep"> | </span>
			<?php printf( __( 'Theme: %1$s by %2$s.', 'selvatica' ), 'Selvatica', '<a href="http://me.com" rel="designer">Eric Barbosa Jr.</a>' ); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
