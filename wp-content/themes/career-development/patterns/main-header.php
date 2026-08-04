<?php
/**
 * Title: Main Header (CSIS Off-Canvas Style Dynamic)
 * Slug: career-development/main-header
 */

$logo_url = get_option( 'bkskm_logo_url', 'https://bkd.bangka.go.id/wp-content/uploads/2026/07/Logo-Prima-no-bg.png' );
if ( empty( $logo_url ) && has_custom_logo() ) {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
    if ( $logo ) {
        $logo_url = $logo[0];
    }
}
$home_url  = home_url( '/' );
$site_name = get_bloginfo( 'name' );
?>
<header class="site-header">

    <!-- Top Bar Area (Kontak & Sosmed) -->
    <div class="top-bar-area bg-dark text-light" id="topBar">
        <div class="container">
            <div class="row align-center">
                <div class="col-lg-8 col-md-8 item-flex">
                    <div class="info">
                        <ul>
                            <li><span class="dashicons dashicons-location"></span> Jl. Soekarno Hatta No. 1, Sungailiat</li>
                            <li><span class="dashicons dashicons-phone"></span> (0717) 92523</li>
                            <li><span class="dashicons dashicons-email"></span> bkpsdmd@bangka.go.id</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 text-right">
                    <div class="social">
                        <ul>
                            <li><a href="https://facebook.com" target="_blank" aria-label="Facebook"><span class="dashicons dashicons-facebook"></span></a></li>
                            <li><a href="https://instagram.com" target="_blank" aria-label="Instagram"><span class="dashicons dashicons-instagram"></span></a></li>
                            <li><a href="https://youtube.com" target="_blank" aria-label="YouTube"><span class="dashicons dashicons-video-alt3"></span></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar mobile-sidenav navbar-style-one navbar-sticky navbar-default validnavs white navbar-fixed on menu-center no-full" id="mainNavbar">
        <div class="container">
            <div class="row align-center">

                <!-- Logo Section -->
                <div class="col-xl-2 col-lg-3 col-md-2 col-sm-1 col-1">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="<?php echo esc_url( $home_url ); ?>">
                            <?php if ( ! empty( $logo_url ) ) : ?>
                                <img src="<?php echo esc_url( $logo_url ); ?>" class="logo" alt="<?php echo esc_attr( $site_name ); ?>">
                            <?php else : ?>
                                <span class="logo-title"><?php echo esc_html( $site_name ); ?></span>
                            <?php endif; ?>
                        </a>
                        <button type="button" class="navbar-toggle" id="navToggleOpen" aria-label="Toggle Navigation">
                            <span class="dashicons dashicons-menu"></span>
                        </button>
                    </div>
                </div>

                <!-- Navigation Menu Section (Desktop) -->
                <div class="col-xl-8 offset-xl-1 col-lg-6 col-md-4 col-sm-4 col-4 d-none d-lg-block">
                    <div class="navbar-collapse">
                        <?php
                        if ( has_nav_menu( 'primary' ) ) {
                            wp_nav_menu( array(
                                'theme_location' => 'primary',
                                'menu_id'        => 'menu-primary-menu',
                                'menu_class'     => 'nav navbar-nav navbar-center',
                                'container'      => false,
                                'fallback_cb'    => false,
                            ) );
                        }
                        ?>
                    </div>
                </div>

                <!-- Language Switcher Section (ID | EN) -->
                <div class="col-xl-1 d-none d-lg-block">
                    <div class="navbar-collapse">
                        <ul class="nav navbar navbar-center lang-switcher">
                            <li><a href="<?php echo esc_url( $home_url ); ?>" class="lang-btn active">ID</a></li>
                            <li><span class="lang-sep">|</span></li>
                            <li><a href="<?php echo esc_url( home_url( '/en/' ) ); ?>" class="lang-btn">EN</a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <!-- Off-canvas Mobile Menu Panel -->
    <div class="mobile-menu-panel" id="mobileMenuPanel">
        <div class="mobile-menu-header">
            <a class="navbar-brand" href="<?php echo esc_url( $home_url ); ?>">
                <?php if ( ! empty( $logo_url ) ) : ?>
                    <img src="<?php echo esc_url( $logo_url ); ?>" class="logo" alt="<?php echo esc_attr( $site_name ); ?>">
                <?php else : ?>
                    <span class="logo-title"><?php echo esc_html( $site_name ); ?></span>
                <?php endif; ?>
            </a>
            <button type="button" class="mobile-menu-close" id="navToggleClose" aria-label="Close Navigation">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="mobile-menu-body">
            <?php
            if ( has_nav_menu( 'primary' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'mobile-menu-list',
                    'container'      => false,
                    'fallback_cb'    => false,
                ) );
            }
            ?>
        </div>
        <div class="mobile-lang-switcher">
            <a href="<?php echo esc_url( $home_url ); ?>" class="lang-btn active">ID</a>
            <span class="lang-sep">|</span>
            <a href="<?php echo esc_url( home_url( '/en/' ) ); ?>" class="lang-btn">EN</a>
        </div>
    </div>

    <!-- Overlay saat menu mobile terbuka -->
    <div class="overlay-screen" id="overlayScreen"></div>

</header>