<?php
/**
 * Plugin Name: RyanCV Plugin
 * Plugin URI: https://ryancv.bslthemes.com/
 * Description: This plugin it's designed for RyanCV Theme
 * Version: 1.0.7
 * Author: beshleyua
 * Author URI: https://bslthemes.com/
 * Text Domain: ryancv-plugin
 * Domain Path: /language/
 * License: http://www.gnu.org/licenses/gpl.html
 */

/* Load plugin text-domain */
function ryancv_plugin_load_textdomain() {
	load_plugin_textdomain( 'ryancv-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'ryancv_plugin_load_textdomain' );

/* Custom Post Types */
require plugin_dir_path( __FILE__ ) . 'custom-post-types.php';

/* ACF RyanCV fields extention */
require plugin_dir_path( __FILE__ ) . 'acf-ext/acf-ui-google-font/acf-ui-google-font.php';
require plugin_dir_path( __FILE__ ) . 'acf-ext/acf-ionicons/acf-ionicons.php';
require plugin_dir_path( __FILE__ ) . 'acf-ext/acf-cf7/acf-cf7.php';

/* Include Elementor Functions */
require_once plugin_dir_path( __FILE__ ) . 'elementor/functions.php';

/**
 * Enabled Custom Post Type Elementor Supports
 */
function ryancv_elementor_cpt_support() {
    $cpt_support = get_option( 'elementor_cpt_support' );

	if( ! $cpt_support ) {
	    $cpt_support = [ 'page', 'post', 'portfolio' ];
	    update_option( 'elementor_cpt_support', $cpt_support );
	} else if( ! in_array( 'portfolio', $cpt_support ) ) {
	    $cpt_support[] = 'portfolio';
	    update_option( 'elementor_cpt_support', $cpt_support );
	}
}
function ryancv_elementor_disable_fonts_and_colors() {
	$color_schemes = get_option( 'elementor_disable_color_schemes' );
	$typography_schemes = get_option( 'elementor_disable_typography_schemes' );

	if( ! $color_schemes ) {
	    update_option( 'elementor_disable_color_schemes', 'yes' );
	}
	if( ! $typography_schemes ) {
	    update_option( 'elementor_disable_typography_schemes', 'yes' );
	}	
}

/* Update permalink structure when plugin is activated */
function ryancv_plugin_activate() {
	update_option( 'rewrite_rules', '' );
	ryancv_elementor_cpt_support();
	ryancv_elementor_disable_fonts_and_colors();
}
function ryancv_plugin_deactivate() {
	update_option( 'rewrite_rules', '' );
}

register_activation_hook( __FILE__, 'ryancv_plugin_activate' );
register_deactivation_hook( __FILE__, 'ryancv_plugin_deactivate' );

?>