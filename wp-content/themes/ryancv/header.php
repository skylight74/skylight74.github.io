<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until content block
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ryancv
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<?php
		$sidebar_disable = get_field( 'sidebar_disable', 'options' );
		$layout_type = get_field( 'layout_type', 'options' );
		$onepage = get_field( 'onepage', 'options' );
		$simple_vcard = get_field( 'simple_vcard', 'options' );
		$mobile_vcard = get_field( 'mobile_vcard', 'options' );
		$sticky_menu = get_field( 'sticky_menu', 'options' );
		$theme_bg = get_field( 'theme_bg', 'options' );
		$animation = get_field( 'theme_animation', 'options' );
		$animation_in = 'fadeInLeft';
		$animation_out = 'fadeOutLeft';
		$theme_style = get_field( 'theme_style', 'options' );
		$theme_ui = get_field( 'theme_ui', 'options' );
		$menu_style = get_field( 'menu_type', 'options' );
		$icons_style = get_field( 'icons_type', 'options' );
		$preloader_hide = get_field( 'preloader_hide', 'options' );
		$preloader_type = get_field( 'preloader_type', 'options' );
		$preloader_bgcolor = get_field( 'preloader_bgcolor', 'options' );

		switch ( $animation ) {
			case 0 :
				$animation_in = 'fadeInLeft';
				$animation_out = 'fadeOutLeft';
				break;
			case 1 :
				$animation_in = 'rotateInUpLeft';
				$animation_out = 'rotateOutUpLeft';
				break;
			case 2 :
				$animation_in = 'rollIn';
				$animation_out = 'rollOut';
				break;
			case 3 :
				$animation_in = 'jackInTheBox';
				$animation_out = 'jackOutTheBox';
				break;
			case 4 :
				$animation_in = 'fadeIn';
				$animation_out = 'fadeOut';
				break;
			case 5 :
				$animation_in = 'fadeInUp';
				$animation_out = 'fadeOutUp';
				break;
		}
	?>

	<div class="page page_wrap<?php if ( $simple_vcard ) : ?> simplecard-wrap-enabled<?php endif; ?><?php if ( $theme_style ) : ?> theme-style-classic<?php endif; ?><?php if ( $theme_ui ) : ?> theme-style-dark<?php endif; ?>">

		<!-- Preloader -->
		<div class="preloader<?php if ( $preloader_hide == 1 ) : ?> is-disabled<?php endif; ?>"<?php if ( $preloader_bgcolor ) : ?> style="background-color: <?php echo esc_attr( $preloader_bgcolor ); ?>;"<?php endif; ?>>
			<div class="centrize full-width">
				<div class="vertical-center">
					<div class="spinner <?php if ( $preloader_type == 1 || !$preloader_type ) : ?>default-circle<?php endif; ?><?php echo esc_attr($preloader_type ); ?>"></div>
				</div>
			</div>
		</div>

		<!-- background -->
		<?php if ( $theme_bg['type'] == 1 || $theme_bg['type'] == 2 || $theme_bg['type'] == 3 ) : ?>
		<div class="background <?php if ( $theme_bg['type'] == 2 ) : ?>gradient<?php endif; ?>">
			<?php if ( $theme_bg['type'] == 2 ) : ?>
			<ul class="bg-bubbles">
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
				<li></li>
			</ul>
			<?php endif; ?>
		</div>
		<?php else : ?>
			<?php if ( class_exists( 'RyanCVAdvancedBackground' ) ) : echo do_shortcode( '[ryancv-advanced-background-html]' ); endif; ?>
		<?php endif; ?>

		<!--
			Container
		-->
		<div class="container opened
		<?php if ( $simple_vcard ) : ?> simplecard-enabled<?php endif; ?>
		<?php if ( $mobile_vcard ) : ?> hide-mobile-vcard<?php endif; ?>
		<?php if ( $sidebar_disable ) : ?> disable-sidebar<?php endif; ?>
		<?php if ( ! $sticky_menu ) : ?> no-sticky-menu<?php endif; ?>
		<?php if ( $layout_type == 1 ) : ?> layout-rounded-style<?php endif; ?>
		<?php if ( $layout_type == 2 ) : ?> layout-boxed-style<?php endif; ?>
		<?php if ( $icons_style == 1 ) : ?> solid-icons-style<?php endif; ?>
		<?php if ( $icons_style == 2 ) : ?> border-icons-style<?php endif; ?>
		<?php if ( $icons_style == 3 ) : ?> minimal-icons-style<?php endif; ?>" data-animation-in="<?php echo esc_attr( $animation_in ); ?>" data-animation-out="<?php echo esc_attr( $animation_out ); ?>">
			<?php
				$vcard_bg = get_field( 'vcard_bg', 'options' );
				$vcard_bg_type = get_field( 'vcard_bg_type', 'options' );
				$vcard_img_layout = get_field( 'vcard_img_layout', 'options' );
				$vcard_bg_video = get_field( 'vcard_bg_video', 'options' );
				$vcard_bg_images = get_field( 'vcard_bg_images', 'options' );
				$vcard_photo = get_field( 'vcard_photo', 'options' );
				$vcard_title = get_field( 'vcard_title', 'options' );
				if ( empty( $vcard_title ) ) {
					$vcard_title = get_bloginfo( 'name' );
				}
				$vcard_subtitle = get_field( 'vcard_subtitle', 'options' );
				if ( empty( $vcard_subtitle ) ) {
					$vcard_subtitle = get_bloginfo( 'description' );
				}
				$vcard_subtitle_type = get_field( 'vcard_subtitle_type', 'options' );
				$vcard_subtitles = get_field( 'vcard_subtitles', 'options' );
				$vcard_social = get_field( 'vcard_social', 'options' );
				$vcard_bts = get_field( 'vcard_bts', 'options' );
				$vcard_bts_style = get_field( 'vcard_bts_style', 'options' );
			?>

			<!--
				Header
			-->
			<header class="header">

				<!-- header profile -->
				<div class="profile">
					<?php if ( $vcard_photo ) : ?>
					<div class="image">
						<a href="<?php echo esc_url( home_url() ); ?>">
							<img src="<?php echo esc_url( $vcard_photo['sizes']['ryancv_140x140'] ); ?>" alt="<?php echo esc_attr( $vcard_title ); ?>" /></a>
						</a>
					</div>
					<?php endif; ?>

					<?php if ( $vcard_title ) : ?>
					<div class="title"><?php echo esc_html( $vcard_title ); ?></div>
					<?php endif; ?>

					<?php if ( $vcard_subtitle ||  $vcard_subtitles) : ?>
						<?php if( $vcard_subtitle_type == 2 ) : ?>
							<div class="subtitle subtitle-typed">
								<div class="typing-title">
									<?php foreach( $vcard_subtitles as $item ) { ?>
										<p><?php echo esc_html( $item['text'] ); ?></p>
									<?php } ?>
								</div>
							</div>
						<?php else : ?>
							<div class="subtitle">
								<?php echo esc_html( $vcard_subtitle ); ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<!-- menu btn -->
				<a href="#" class="menu-btn"><span></span></a>

				<!-- Woocommerce cart -->
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<?php if ( true == get_theme_mod( 'cart_shop', true ) ) : ?>
						<div class="cart-btn">
							<div class="cart-icon">
								<span class="ion ion-android-cart"></span>
								<span class="cart-count"><?php echo sprintf (_n( '%d', '%d', WC()->cart->get_cart_contents_count(), 'ryancv' ), WC()->cart->get_cart_contents_count() ); ?></span>
							</div>
							<div class="cart-widget">
								<?php woocommerce_mini_cart(); ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<!-- menu -->
				<?php if ( $sticky_menu ) : ?>
					<?php if ( $onepage ) : ?>
						<!-- menu -->
						<div class="top-menu top-menu-onepage<?php if ( $menu_style == 1 ) : ?> menu-minimal<?php endif; ?>">
							<?php
								wp_nav_menu( array(
									'theme_location' => 'primary',
									'walker' => new Ryancv_Onepage_Walker()
								) );
							?>
						</div>
					<?php else : ?>
						<!-- menu -->
						<div class="top-menu<?php if ( $menu_style == 1 ) : ?> menu-minimal<?php endif; ?>">
							<?php
								wp_nav_menu( array(
									'theme_location' => 'primary'
								) );
							?>
						</div>
					<?php endif; ?>
				<?php endif; ?>

			</header>

			<!--
				Card - Started
			-->
			<div class="card-started" id="home-card">

				<!--
					Profile
				-->
				<div class="profile
				<?php if ( ! $vcard_photo ) : ?> no-photo<?php endif; ?>
				<?php if ( $vcard_img_layout == 1 ) : ?> boxed-style<?php endif; ?>
				<?php if ( $vcard_img_layout == 2 ) : ?> rounded-style-1<?php endif; ?>
				<?php if ( $vcard_img_layout == 3 ) : ?> rounded-style-2<?php endif; ?>
				<?php if ( $vcard_img_layout == 4 ) : ?> rabbet-style<?php endif; ?>
				<?php if ( $vcard_img_layout == 5 ) : ?> trapezoid-style<?php endif; ?>">
					<div class="profile-content">

						<?php if ( $vcard_bg_type == 0 || !$vcard_bg_type ) : ?>
						<!-- profile image -->
						<div class="slide"
							<?php if ( $vcard_bg ) : ?>style="background-image: url(<?php echo esc_url( $vcard_bg['url'] ); ?>);"<?php endif; ?>
						>
						</div>
						<?php endif; ?>

						<?php if ( $vcard_bg_type == 1 ) : ?>
						<!-- profile video -->
						<div class="slide">
							<?php if ( $vcard_bg_video ) : ?>
							<video autoplay muted loop controls playsinline id="myVideo">
								<source src="<?php echo esc_url( $vcard_bg_video ); ?>" type="video/mp4">
							</video>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<!-- profile slideshow -->
						<?php if ( $vcard_bg_type == 2 ) : ?>
						<div class="slide">
							<?php if ( $vcard_bg_images ) : ?>
							<div class="swiper-container ryan-slideshow">
								<div class="swiper-wrapper">
									<?php foreach ( $vcard_bg_images as $slide ) : $slide_url = wp_get_attachment_image_url( $slide['image'], 'ryancv_600xauto' ); ?>
									<div class="swiper-slide">
										<img src="<?php echo esc_url( $slide_url ); ?>" alt="<?php echo esc_attr( $vcard_title ); ?>" class="ryan-banner-cover" data-swiper-parallax-y="-30" data-swiper-parallax-scale="1.2">
									</div>
									<?php endforeach; ?>
								</div>
							</div>
							<?php endif; ?>
						</div>
						<?php endif; ?>

						<?php if ( $vcard_photo ) : ?>
						<!-- profile photo -->
						<div class="image">
							<a href="<?php echo esc_url( home_url() ); ?>">
								<img src="<?php echo esc_url( $vcard_photo['sizes']['ryancv_280x280'] ); ?>" alt="<?php echo esc_attr( $vcard_title ); ?>" />
							</a>
						</div>
						<?php endif; ?>

						<!-- profile titles -->
						<?php if ( $vcard_title ) : ?>
						<div class="title"><?php echo esc_html( $vcard_title ); ?></div>
						<?php endif; ?>

						<?php if ( $vcard_subtitle ||  $vcard_subtitles) : ?>
						<?php if( $vcard_subtitle_type == 2 ) : ?>
						<div class="subtitle subtitle-typed">
							<div class="typing-title">
								<?php foreach( $vcard_subtitles as $item ) { ?>
									<p><?php echo esc_html( $item['text'] ); ?></p>
								<?php } ?>
							</div>
						</div>
						<?php else : ?>
						<div class="subtitle">
							<?php echo esc_html( $vcard_subtitle ); ?>
						</div>
						<?php endif; ?>
						<?php endif; ?>

						<?php if ( $vcard_social ) : ?>
						<!-- profile socials -->
						<div class="social">
							<?php foreach ( $vcard_social as $item ) { ?>
							<a target="_blank" href="<?php echo esc_url( $item['url'] ); ?>">
								<span class="fab <?php echo esc_attr( $item['icon'] ); ?>"></span>
							</a>
							<?php } ?>
						</div>
						<?php endif; ?>

					</div>

					<?php if ( $vcard_bts && $sticky_menu || $simple_vcard ) : ?>
					<!-- profile buttons -->
					<div class="lnks">
						<?php foreach ( $vcard_bts as $item ) { ?>
						<?php if ( $item['url'] ) : ?>
						<a href="<?php echo esc_url( $item['url']['url'] ); ?>" class="lnk<?php if( $vcard_bts_style == 1 ) : ?> solid-style<?php endif; ?>" <?php if ( $item['url']['target'] ) : ?>target="<?php echo esc_attr( $item['url']['target'] ); ?>"<?php endif; ?>>
							<span class="text"><?php echo esc_html( $item['text'] ); ?></span>
							<?php if ( $item['icon'] != 'ion-none' ) : ?>
							<span class="ion <?php echo esc_attr( $item['icon'] ); ?>"></span>
							<?php endif; ?>
						</a>
						<?php else : ?>
						<a href="#" class="lnk">
							<span class="text"><?php echo esc_html( $item['text'] ); ?></span>
							<?php if ( $item['icon'] != 'ion-none' ) : ?>
							<span class="ion <?php echo esc_attr( $item['icon'] ); ?>"></span>
							<?php endif; ?>
						</a>
						<?php endif; ?>
						<?php } ?>
					</div>
					<?php else : ?>
					<!-- default menu -->
					<div class="main-menu-fixed">
						<div class="main-menu">
							<?php
								wp_nav_menu( array(
									'theme_location' => 'primary'
								) );
							?>
						</div>
					</div>
					<!-- menu button -->
					<div class="lnks">
						<a href="#" class="lnk lnk-view-menu">
							<span class="ion ion-android-more-horizontal"></span>
							<span class="text" data-text-open="<?php echo esc_attr__( 'Close', 'ryancv' ); ?>"><?php echo esc_html__( 'Menu', 'ryancv' ); ?></span>
						</a>
					</div>
					<?php endif; ?>

				</div>

			</div>

			<div class="s_overlay"></div>
			<div class="content-sidebar">
				<div class="sidebar-wrap">
					<?php if ( ! $sticky_menu ) : ?>
					<div class="main-menu">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'primary'
							) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( ! $sidebar_disable && is_active_sidebar( 'sidebar-1' ) ) : ?>
						<?php get_sidebar(); ?>
					<?php endif; ?>
				</div>

				<span class="close"></span>
			</div>
