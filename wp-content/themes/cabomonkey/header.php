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

				<!-- left menu toggle -->
				<button class="menu-toggle" aria-controls="menu" aria-expanded="false"></button>

				<!-- chat button at the center top -->
				<div class="chat"></div>

				<!-- right menu toggle -->
				<div class="menu-toggle-2"></div>

				<!-- small logo to the right -->
				<div class="logo">
					<a href="<?php echo site_url('/'); ?>">
						<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/logo-2.png" />
					</a>
				</div>

				<div class="menu-block">

					<!-- Big logo on circle -->
					<div class="site-branding">
					</div>

					<!-- 2 menus but only one will be visible at a time. -->
					<?php wp_nav_menu( array( 'theme_location' => 'primary' ) ); ?>
					<?php wp_nav_menu( array( 'theme_location' => 'secondary' ) ); ?>
				</div>

			</nav>

		</div>
		<!-- End Content Header -->

		<!-- Banner, large video -->
		<div class="banner">
			<iframe src="//player.vimeo.com/video/119495554" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
		</div>
		<!-- End Banner -->

		<!-- Banner 2, text to the left and image buttons to the right. -->
		<div class="banner-2">

			<!-- Text with custom fonts. -->
			<div class="reasons">
				<div class="texts">
					<div class="text text-1">3 REASONS YOU</div>
					<div class="text text-2">SHOULD BUY</div>
					<div class="text text-3">FROM <span>CABOMONKEY!!!</span></div>
				</div>
			</div>

			<!-- Image buttons (circles) -->
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

		<!-- Banner 3, cactus to the left and tripadvisor to the right. -->
		<div class="banner-3">

			<div class="image">

				<!-- Cactus Image -->
				<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-6.png" />

				<!-- White Text -->
				<div class="inner">
					<div class="texts">
						<div class="text text-1">SUPERCHARGE YOUR VACATION!</div>
						<div class="text text-2">CHOOSE CABOMONKEY!</div>
						<div class="text text-3">Exceeding Your Expectations!!!</div>
					</div>
				</div>

			</div>

			<!-- TripAdvisor Review -->
			<div class="tripadvisor">

				<!-- tripadvisor logo -->
				<div class="tripadvisor_image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/tripadvisor.png" />
				</div>

				<!-- Review widget -->
				<div id="TA_cdswritereviewlg615" class="TA_cdswritereviewlg  preload-hide">
					<ul id="AvarNuY" class="TA_links SfBMBEUlon">
						<li id="Rjy2LZ" class="s5j7dus">
							<a target="_blank" href="http://www.tripadvisor.com/"><img src="http://www.tripadvisor.com/img/cdsi/img2/branding/medium-logo-12097-2.png" alt="TripAdvisor"/></a>
						</li>
					</ul>
				</div>
				<script src="http://www.jscache.com/wejs?wtype=cdswritereviewlg&amp;uniq=615&amp;locationId=1148450&amp;lang=en_US&amp;border=true&amp;display_version=2"></script>

			</div>

		</div>
		<!-- End Banner 3 -->
