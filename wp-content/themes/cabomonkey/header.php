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

	<header id="masthead" class="site-header columns large-3" role="banner">
		<div class="site-branding">
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
		</div><!-- .site-branding -->

		<nav id="site-navigation" class="main-navigation" role="navigation">
			<button class="menu-toggle" aria-controls="menu" aria-expanded="false"><?php _e( 'Primary Menu', 'cabomonkey' ); ?></button>
			<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->

	<div id="content" class="site-content columns large-9">
		<div class="header">
			<div class="chat columns large-2 large-centered">
				CHAT NOW
			</div>
			<div class="logo colums large-3 large-offset-9">
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-2.png" />
			</div>
		</div>
		<div class="banner"></div>
		<div class="banner-2"></div>

		<div class="banner-3">
			<div class="image columns large-8"></div>
			<div class="tripadvisor columns large-3"></div>
		</div>
