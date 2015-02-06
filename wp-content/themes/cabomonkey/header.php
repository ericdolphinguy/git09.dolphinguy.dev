<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package CaboMonkey
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site row collapse">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'cabomonkey' ); ?></a>

	<header id="masthead" class="site-header" role="banner">



	</header><!-- #masthead -->

	<div id="content" class="site-content columns large-12 medium-12">

		<!-- Content Header -->
		<div class="header">

			<nav id="site-navigation" class="main-navigation" role="navigation">

				<button class="menu-toggle" aria-controls="menu" aria-expanded="false"><?php _e( 'Primary Menu', 'cabomonkey' ); ?></button>

				<div class="chat">
				</div>

				<div class="logo">
					<a href="<?php echo site_url('/'); ?>">
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-2.png" />
					</a>
				</div>

				<div class="site-branding">
				</div><!-- .site-branding -->

				<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>

			</nav><!-- #site-navigation -->

			<!--<div class="chat">
			</div>

			<div class="logo">
				<a href="<?php /*echo site_url('/'); */?>">
					<img src="<?php /*echo get_stylesheet_directory_uri(); */?>/images/logo-2.png" />
				</a>
			</div>-->

		</div>
		<!-- End Content Header -->



		<!-- Banner -->
		<div class="banner">
			<div class="text">
				<div class="text-1">
					WELCOME
				</div>
				<div class="text-2">
					TO THE LAST
				</div>
				<div class="text-3">
					EXCITING
				</div>
				<div class="text-4">
					PART OF YOUR ADVENTURE!!!
				</div>
			</div>
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image.jpg" />
		</div>
		<!-- End Banner -->

		<!-- Banner 2 -->
		<div class="banner-2">
			<div class="reasons">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/reasons.png" />
			</div>
			<div class="guarantees">
				<!--<img src="<?php /*echo get_stylesheet_directory_uri(); */?>/images/guarantees.png" />-->
				<div class="guarantee">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/good-weather.png" />
				</div>
				<div class="guarantee">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/free-pictures.png" />
				</div>
				<div class="guarantee">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/30-min-cancels.png" />
				</div>
			</div>
		</div>
		<!-- End Banner 2 -->

		<!-- Banner 3 -->
		<div class="banner-3">
			<div class="image">
				<div class="text">
					<div class="text-1">SUPERCHARGE YOUR VACATION</div>
					<div class="text-2">CHOOSE CABOMONKEY</div>
				</div>

				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-3.png" />
			</div>
			<div class="tripadvisor">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-4.png" />
			</div>
		</div>
		<!-- End Banner 3 -->
