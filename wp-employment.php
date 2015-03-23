<?php
/*
 * Plugin Name: WordPress Job Openings & Application Management
 * Version: 0.5.0
 * Plugin URI: https://github.com/aaronhuisinga/wordpress-employment
 * Description: A WordPress plugin allowing administrators and editors to post available job openings, accept resumes & applications, and send batch emails to applicants.
 * Author: Aaron Huisinga
 * Author URI: https://github.com/aaronhuisinga
 * Requires at least: 4.0
 * Tested up to: 4.1
 *
 * @package WordPress Job Openings
 * @author Aaron Huisinga
 * @since 0.5.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! defined( 'WP_EMPLOYMENT_PLUGIN_PATH' ) )
	define( 'WP_EMPLOYMENT_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

require_once( 'classes/class-wp-employment.php' );

global $wp_employment;
$wp_employment = new WP_Employment( __FILE__ );

if ( is_admin() ) {
	require_once( 'classes/class-wp-employment-admin.php' );
	$wp_employment_admin = new WP_Employment_Admin( __FILE__ );
}
