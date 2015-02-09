<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package CaboMonkey
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<!-- Every row is an offer -->
			<div class="offer ">

				<!-- Big image on the left side. -->
				<div class="image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cabo-adventures-cactus-atv-tours-01.jpg" />
					<div class="meta">
						<div class="qrcode">
							<?php echo do_shortcode( sprintf( '[qrcode size=100 content="%s"]', site_url( 'cabo-adventures-cactus-atv-tours-01' ) ) ); ?>
						</div>
						<div class="notes">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-12.png" />
						</div>
					</div>
				</div>

				<!-- The coupon/ticket section to the right. -->
				<div class="coupon">

					<!-- Trip advisor sub section. -->
					<div class="tripadvisor">
						<div class="text">
							<div class="rank">
								<strong>Ranked #1</strong> of 54 attactions
							</div>
							<div class="rank-value">
								<div class="stars"></div>
								<div class="reviews">2,261 reviews</div>
							</div>
						</div>
					</div>

					<div class="inner">
						<div class="title">
							CACTUS ATV TOURS
						</div>
						<div class="title-2">
							The Adventure
						</div>

						<div class="text">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur in iusto neque quasi ut. Amet at cumque deleniti eaque incidunt ipsum, nobis tenetur! Accusantium commodi doloremque fugit itaque maiores quasi!
						</div>

						<div class="buttons clear">
							<div class="details buttn">Details</div>
							<div class="bookit buttn">Book It</div>   
						</div>

						<!-- Ticket secion -->
						<div class="ticket">
							<div class="adults">
								<div class="price1">$150</div>
								<div class="price2">$125</div>
								<div class="category">ADULTS</div>
							</div>
							<div class="kids">
								<div class="price1">$100</div>
								<div class="price2">$75</div>
								<div class="category">KIDS</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="offer ">
				<div class="image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cabo-adventures-zip-line-adventure-01.jpg" />
					<div class="meta">
						<div class="qrcode">
							<?php echo do_shortcode( sprintf( '[qrcode size=100 content="%s"]', site_url( 'cabo-adventures-cactus-atv-tours-01' ) ) ); ?>
						</div>
						<div class="notes">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-12.png" />
						</div>
					</div>
				</div>
				<div class="coupon">
					<div class="tripadvisor">
						<div class="text">
							<div class="rank">
								<strong>Ranked #1</strong> of 54 attactions
							</div>
							<div class="rank-value">
								<div class="stars"></div>
								<div class="reviews">2,261 reviews</div>
							</div>
						</div>
					</div>
					<div class="inner">
						<div class="title">
							OUTDOOT ZIPLINE ADVENTURE
						</div>
						<div class="title-2">
							The Adventure
						</div>
						<div class="text">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur in iusto neque quasi ut. Amet at cumque deleniti eaque incidunt ipsum, nobis tenetur! Accusantium commodi doloremque fugit itaque maiores quasi!
						</div>
						<div class="buttons clear">
							<div class="details buttn">Details</div>
							<div class="bookit buttn">Book It</div>
						</div>
						<div class="ticket">
							<div class="adults">
								<div class="price1">$150</div>
								<div class="price2">$125</div>
								<div class="category">ADULTS</div>
							</div>
							<div class="kids">
								<div class="price1">$100</div>
								<div class="price2">$75</div>
								<div class="category">KIDS</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="offer ">
				<div class="image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cabo-adventures-outback-camel-safari-01.jpg" />
					<div class="meta">
						<div class="qrcode">
							<?php echo do_shortcode( sprintf( '[qrcode size=100 content="%s"]', site_url( 'cabo-adventures-cactus-atv-tours-01' ) ) ); ?>
						</div>
						<div class="notes">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-12.png" />
						</div>
					</div>
				</div>
				<div class="coupon">
					<div class="tripadvisor">
						<div class="text">
							<div class="rank">
								<strong>Ranked #1</strong> of 54 attactions
							</div>
							<div class="rank-value">
								<div class="stars"></div>
								<div class="reviews">2,261 reviews</div>
							</div>
						</div>
					</div>
					<div class="inner">
						<div class="title">
							OUTBACK CAMEL SAFARI
						</div>
						<div class="title-2">
							The Adventure
						</div>
						<div class="text">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur in iusto neque quasi ut. Amet at cumque deleniti eaque incidunt ipsum, nobis tenetur! Accusantium commodi doloremque fugit itaque maiores quasi!
						</div>
						<div class="buttons clear">
							<div class="details buttn">Details</div>
							<div class="bookit buttn">Book It</div>
						</div>
						<div class="ticket">
							<div class="adults">
								<div class="price1">$150</div>
								<div class="price2">$125</div>
								<div class="category">ADULTS</div>
							</div>
							<div class="kids">
								<div class="price1">$100</div>
								<div class="price2">$75</div>
								<div class="category">KIDS</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="offer ">
				<div class="image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cabo-adventures-yacht-rentals-01.jpg" />
					<div class="meta">
						<div class="qrcode">
							<?php echo do_shortcode( sprintf( '[qrcode size=100 content="%s"]', site_url( 'cabo-adventures-cactus-atv-tours-01' ) ) ); ?>
						</div>
						<div class="notes">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-12.png" />
						</div>
					</div>
				</div>
				<div class="coupon">
					<div class="tripadvisor">
						<div class="text">
							<div class="rank">
								<strong>Ranked #1</strong> of 54 attactions
							</div>
							<div class="rank-value">
								<div class="stars"></div>
								<div class="reviews">2,261 reviews</div>
							</div>
						</div>
					</div>
					<div class="inner">
						<div class="title">
							CABO YATCH RENTALS
						</div>
						<div class="title-2">
							The Adventure
						</div>
						<div class="text">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur in iusto neque quasi ut. Amet at cumque deleniti eaque incidunt ipsum, nobis tenetur! Accusantium commodi doloremque fugit itaque maiores quasi!
						</div>
						<div class="buttons clear">
							<div class="details buttn">Details</div>
							<div class="bookit buttn">Book It</div>
						</div>
						<div class="ticket">
							<div class="adults">
								<div class="price1">$150</div>
								<div class="price2">$125</div>
								<div class="category">ADULTS</div>
							</div>
							<div class="kids">
								<div class="price1">$100</div>
								<div class="price2">$75</div>
								<div class="category">KIDS</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="offer ">
				<div class="image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cabo-adventures-santa-maria-snorkeling-01.jpg" />
					<div class="meta">
						<div class="qrcode">
							<?php echo do_shortcode( sprintf( '[qrcode size=100 content="%s"]', site_url( 'cabo-adventures-cactus-atv-tours-01' ) ) ); ?>
						</div>
						<div class="notes">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-12.png" />
						</div>
					</div>
				</div>
				<div class="coupon">
					<div class="tripadvisor">
						<div class="text">
							<div class="rank">
								<strong>Ranked #1</strong> of 54 attactions
							</div>
							<div class="rank-value">
								<div class="stars"></div>
								<div class="reviews">2,261 reviews</div>
							</div>
						</div>
					</div>
					<div class="inner">
						<div class="title">
							SANTA MARIA SNORKELING
						</div>
						<div class="title-2">
							The Adventure
						</div>
						<div class="text">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur in iusto neque quasi ut. Amet at cumque deleniti eaque incidunt ipsum, nobis tenetur! Accusantium commodi doloremque fugit itaque maiores quasi!
						</div>
						<div class="buttons clear">
							<div class="details buttn">Details</div>
							<div class="bookit buttn">Book It</div>
						</div>
						<div class="ticket">
							<div class="adults">
								<div class="price1">$150</div>
								<div class="price2">$125</div>
								<div class="category">ADULTS</div>
							</div>
							<div class="kids">
								<div class="price1">$100</div>
								<div class="price2">$75</div>
								<div class="category">KIDS</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="offer ">
				<div class="image">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/cabo-adventures-cabo-dolphins-01.jpg" />
					<div class="meta">
						<div class="qrcode">
							<?php echo do_shortcode( sprintf( '[qrcode size=100 content="%s"]', site_url( 'cabo-adventures-cactus-atv-tours-01' ) ) ); ?>
						</div>
						<div class="notes">
							<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/image-12.png" />
						</div>
					</div>
				</div>
				<div class="coupon">
					<div class="tripadvisor">
						<div class="text">
							<div class="rank">
								<strong>Ranked #1</strong> of 54 attactions
							</div>
							<div class="rank-value">
								<div class="stars"></div>
								<div class="reviews">2,261 reviews</div>
							</div>
						</div>
					</div>
					<div class="inner">
						<div class="title">
							CABO DOLPHINS
						</div>
						<div class="title-2">
							The Adventure
						</div>
						<div class="text">
							Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consequatur in iusto neque quasi ut. Amet at cumque deleniti eaque incidunt ipsum, nobis tenetur! Accusantium commodi doloremque fugit itaque maiores quasi!
						</div>
						<div class="buttons clear">
							<div class="details buttn">Details</div>
							<div class="bookit buttn">Book It</div>
						</div>
						<div class="ticket">
							<div class="adults">
								<div class="price1">$150</div>
								<div class="price2">$125</div>
								<div class="category">ADULTS</div>
							</div>
							<div class="kids">
								<div class="price1">$100</div>
								<div class="price2">$75</div>
								<div class="category">KIDS</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
