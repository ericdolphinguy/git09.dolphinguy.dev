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
		<?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
	</header><!-- #masthead -->

	<div id="content" class="site-content columns large-12 medium-12">

		<!-- Content Header -->
		<div class="header">

			<nav id="site-navigation" class="main-navigation" role="navigation">

				<!-- Tried to use a div instead of button but the break the script. -->
				<button class="menu-toggle" aria-controls="menu" aria-expanded="false"></button>

				<div class="chat">
				</div><!-- .chat -->

				<div class="menu-toggle-2"></div>

				<div class="logo">
					<a href="<?php echo site_url('/'); ?>">
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-2.png" />
					</a>
				</div><!-- .logo -->

				<div class="menu-block">
					<div class="site-branding">
					</div><!-- .site-branding -->
					<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
					<?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
				</div>

				<div class="texts">
					<div class="text-1">WELCOME</div>
					<div class="text-2">TO THE LEAST</div>
					<div class="text-3">EXCITING</div>
					<div class="text-4">PART OF YOUR ADVENTURE!!!</div>
				</div>

			</nav>

		</div>
		<!-- End Content Header -->

		<!-- Banner -->
		<div class="banner">
			<!--<img src="<?php /*echo get_stylesheet_directory_uri(); */?>/images/image.jpg" />-->
			<iframe width="560" height="315" src="https://www.youtube.com/embed/HrqXTHCGVEo?rel=0&amp;controls=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
		</div>
		<!-- End Banner -->

		<!-- Banner 2 -->
		<div class="banner-2">
			<div class="reasons">
				<div class="texts">
					<div class="text text-1">3 REASONS YOU</div>
					<div class="text text-2">SHOULD BUY</div>
					<div class="text text-3">FROM <span>CABOMONKEY!!!</span></div>
				</div>
			</div>
			<div class="guarantees">
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
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-6.png" />

				<div class="inner">
					<div class="texts">
						<div class="text text-1">SUPERCHARGE YOUR VACATION!</div>
						<div class="text text-2">CHOOSE CABOMONKEY!</div>
					</div>
				</div>
			</div>
			<div class="tripadvisor">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-4.png" />
			</div>
		</div>
		<!-- End Banner 3 -->
