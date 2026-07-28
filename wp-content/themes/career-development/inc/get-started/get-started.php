<?php
add_action( 'admin_menu', 'career_development_getting_started' );
function career_development_getting_started() {
	add_theme_page( esc_html__('Get Started', 'career-development'), esc_html__('Get Started', 'career-development'), 'edit_theme_options', 'career-development-guide-page', 'career_development_test_guide');
}

// Add a Custom CSS file to WP Admin Area
function career_development_admin_theme_style() {
   wp_enqueue_style('custom-admin-style', esc_url(get_template_directory_uri()) . '/inc/get-started/get-started.css');
}
add_action('admin_enqueue_scripts', 'career_development_admin_theme_style');

//guidline for about theme
function career_development_test_guide() { 
	//custom function about theme customizer
	$return = add_query_arg( array()) ;
	$theme = wp_get_theme( 'career-development' );
?>
	<div class="wrapper-outer">
		<div class="left-main-box">
			<div class="intro"><h3><?php echo esc_html( $theme->Name ); ?></h3></div>
			<div class="left-inner">
				<div class="about-wrapper">
					<div class="col-left">
						<p><?php echo esc_html( $theme->get( 'Description' ) ); ?></p>
					</div>
					<div class="col-right">
						<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/screenshot.png" alt="" />
					</div>
				</div>
				<div class="link-wrapper">
					<h4><?php esc_html_e('Important Links', 'career-development'); ?></h4>
					<div class="link-buttons">
						<a class="visit-btn" href="<?php echo esc_url( home_url() ); ?>" target="_blank"><?php esc_html_e('Visit Site', 'career-development'); ?></a>
						<a href="<?php echo esc_url( CAREER_DEVELOPMENT_THEME_DOC ); ?>" target="_blank"><?php esc_html_e('Free Setup Guide', 'career-development'); ?></a>
						<a href="<?php echo esc_url( CAREER_DEVELOPMENT_SUPPORT ); ?>" target="_blank"><?php esc_html_e('Support Forum', 'career-development'); ?></a>
						<a href="<?php echo esc_url( CAREER_DEVELOPMENT_PRO_DEMO ); ?>" target="_blank"><?php esc_html_e('Live Demo', 'career-development'); ?></a>
						<a href="<?php echo esc_url( CAREER_DEVELOPMENT_PRO_THEME_DOC ); ?>" target="_blank"><?php esc_html_e('Pro Setup Guide', 'career-development'); ?></a>
					</div>
				</div>
				<div class="support-wrapper">
					<div class="editor-box">
						<i class="dashicons dashicons-admin-appearance"></i>
						<h4><?php esc_html_e('Theme Customization', 'career-development'); ?></h4>
						<p><?php esc_html_e('Effortlessly modify & maintain your site using editor.', 'career-development'); ?></p>
						<div class="support-button">
							<a class="button button-primary" href="<?php echo esc_url( admin_url( 'site-editor.php' ) ); ?>" target="_blank"><?php esc_html_e('Site Editor', 'career-development'); ?></a>
						</div>
					</div>
					<div class="support-box">
						<i class="dashicons dashicons-microphone"></i>
						<h4><?php esc_html_e('Need Support?', 'career-development'); ?></h4>
						<p><?php esc_html_e('Go to our support forum to help you in case of queries.', 'career-development'); ?></p>
						<div class="support-button">
							<a class="button button-primary" href="<?php echo esc_url( CAREER_DEVELOPMENT_SUPPORT ); ?>" target="_blank"><?php esc_html_e('Get Support', 'career-development'); ?></a>
						</div>
					</div>
					<div class="review-box">
						<i class="dashicons dashicons-star-filled"></i>
						<h4><?php esc_html_e('Leave Us A Review', 'career-development'); ?></h4>
						<p><?php esc_html_e('Are you enjoying Our Theme? We would Love to hear your Feedback.', 'career-development'); ?></p>
						<div class="support-button">
							<a class="button button-primary" href="<?php echo esc_url( CAREER_DEVELOPMENT_REVIEW ); ?>" target="_blank"><?php esc_html_e('Rate Us', 'career-development'); ?></a>
						</div>
					</div>
				</div>
			</div>
			<div class="go-premium-box">
				<h4><?php esc_html_e('Why Go For Premium?', 'career-development'); ?></h4>
				<ul class="pro-list">
					<li><?php esc_html_e('Advanced Customization Options', 'career-development');?></li>
					<li><?php esc_html_e('One-Click Demo Import', 'career-development');?></li>
					<li><?php esc_html_e('WooCommerce Integration & Enhanced Features', 'career-development');?></li>
					<li><?php esc_html_e('Performance Optimization & SEO-Ready', 'career-development');?></li>
					<li><?php esc_html_e('Premium Support & Regular Updates', 'career-development');?></li>
				</ul>
			</div>
		</div>
		<div class="right-main-box">
			<div class="right-inner">
				<div class="pro-boxes">
					<h4><?php esc_html_e('Get Theme Bundle', 'career-development'); ?></h4>
					<p><?php esc_html_e('80+ Premium WordPress Themes', 'career-development'); ?></p>
					<p class="main-bundle-price" ><strong class="cancel-bundle-price"><?php esc_html_e('$2340', 'career-development'); ?></strong><span class="bundle-price"><?php esc_html_e('$86', 'career-development'); ?></span></p>
					<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/bundle.png" alt="bundle image" />
					<p><?php esc_html_e('SUMMER SALE: ', 'career-development'); ?><strong><?php esc_html_e('Extra 20%', 'career-development'); ?></strong><?php esc_html_e(' OFF on WordPress Theme Bundle Use Code: ', 'career-development'); ?><strong><?php esc_html_e('“HEAT20”', 'career-development'); ?></strong></p>
					<a href="<?php echo esc_url( CAREER_DEVELOPMENT_PRO_THEME_BUNDLE ); ?>" target="_blank"><?php esc_html_e('Get Theme Bundle For ', 'career-development'); ?><span><?php esc_html_e('$86', 'career-development'); ?></a>
				</div>
				<div class="pro-boxes pro-theme-container">
					<h4><?php esc_html_e('Career Development Pro', 'career-development'); ?></h4>
					<p class="pro-theme-price" ><?php esc_html_e('$39', 'career-development'); ?></p>
					<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/premium.png" alt="premium image" />
					<p><?php esc_html_e('SUMMER SALE: ', 'career-development'); ?><strong><?php esc_html_e('Extra 25%', 'career-development'); ?></strong><?php esc_html_e(' OFF on WordPress Block Themes! Use Code: ', 'career-development'); ?><strong><?php esc_html_e('“SUMMER25”', 'career-development'); ?></strong></p>
					<a href="<?php echo esc_url( CAREER_DEVELOPMENT_BUY_NOW ); ?>" target="_blank"><?php esc_html_e('Upgrade To Pro At Just at $29.25', 'career-development'); ?></a>
				</div>
				<div class="pro-boxes last-pro-box">
					<h4><?php esc_html_e('View All Our Themes', 'career-development'); ?></h4>
					<img role="img" src="<?php echo esc_url(get_template_directory_uri()); ?>/inc/get-started/images/all-themes.png" alt="all themes image" />
					<a href="<?php echo esc_url( CAREER_DEVELOPMENT_PRO_ALL_THEMES ); ?>" target="_blank"><?php esc_html_e('View All Our Premium Themes', 'career-development'); ?></a>
				</div>
			</div>
		</div>
	</div>
<?php } ?>