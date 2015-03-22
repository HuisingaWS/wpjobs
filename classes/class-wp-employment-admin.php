<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Employment_Admin {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $home_url;
	private $token;

	/**
	 * Basic constructor for the WP Employment Admin class
	 *
	 * @param string $file
	 */
	public function __construct( $file )
	{
		$this->dir        = dirname( $file );
		$this->file       = $file;
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $file ) ) );
		$this->home_url   = trailingslashit( home_url() );
		$this->token      = 'wp_employment';

		// Register plugin settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Add settings page to menu
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		// Display notices in the WP admin
		add_action( 'admin_notices', array( $this, 'admin_notice' ), 10 );
		// Hide WP admin notice
		add_action( 'admin_init', array( $this, 'ignore_admin_notice' ) );
	}

	/**
	 * Add the menu link for the plugin
	 */
	public function add_menu_item()
	{
		add_options_page( 'WP Job Openings Configuration', 'Job Openings', 'publish_posts', $this->token . '_config', 'settings_page' );
	}

	/**
	 * Display admin notice about shortcodes after plugin activation
	 */
	public function admin_notice()
	{
		global $current_user;
		$user_id = $current_user->ID;

		if ( get_user_meta( $user_id, $this->token . '_ignore_notice' ) ) {
			echo '<div class="updated"><p>';
			printf( __( 'The WP Job Openings plugin will not function until pages with the [WPEM] and [EMAPPLY] shortcodes are created. <span style="float: right;"><a href="%1$s">Hide Notice</a></span>' ), '?' . $this->token . '_ignore_notice=1' );
			echo "</p></div>";
		}
	}

	/**
	 * Hide the shortcode notice if user opts out
	 */
	public function ignore_admin_notice()
	{
		global $current_user;
		$user_id = $current_user->ID;

		if ( isset( $_GET[ $this->token . 'ignore_notice' ] ) && $_GET[ $this->token . 'ignore_notice' ] == '1' ) {
			add_user_meta( $user_id, $this->token . 'ignore_notice', 'true', true );
		}
	}

	/**
	 * Register the different settings available
	 * to customize the plugin.
	 */
	public function register_settings()
	{
		// Add settings section
		add_settings_section( 'customize', __( 'Basic Settings', $this->token ), [
			$this,
			'main_settings'
		], $this->token );

		register_setting( 'wpem_optiongroup', 'wpem_options' ); // General Settings

		// Add Settings Fields
		add_settings_field( $this->token . '_companies', __( 'Company Names', $this->token ), array(
			$this,
			'companies_field'
		), $this->token, 'customize' );

		add_settings_field( $this->token . '_applyp', __( 'Application Page', $this->token ), array(
			$this,
			'application_page_field'
		), $this->token, 'customize' );

		add_settings_field( $this->token . '_rname', __( 'Auto Reply From (Name)', $this->token ), array(
			$this,
			'auto_reply_name_field'
		) );

		add_settings_field( $this->token . '_reply', __( 'Auto Reply Content', $this->token ), array(
			$this,
			'auto_reply_content_field'
		), $this->token, 'customize' );

		add_settings_field( $this->token . '_disclaimer', __( 'Application Disclaimer', $this->token ), array(
			$this,
			'disclaimer_field'
		), $this->token, 'customize' );

		// Register settings fields
		register_setting( $this->token, $this->token . '_companies' );
		register_setting( $this->token, $this->token . '_applyp' );
		register_setting( $this->token, $this->token . '_rname' );
		register_setting( $this->token, $this->token . '_reply' );
		register_setting( $this->token, $this->token . '_disclaimer' );

		// Allow plugins to add more settings fields
		do_action( $this->token . '_settings_fields' );
	}

	/**
	 * Define the main description string
	 * for the Settings page.
	 */
	public function main_settings()
	{
		echo '<p>' . __( 'Adjust settings for the employment plugin below. For the companies field, list the names of the different tags that you will give your posts, separated by commas. (Ex. Company1,Company2)', $this->token ) . '</p>';
	}

	/**
	 * Create the companies field for the Settings page.
	 */
	public function companies_field()
	{
		$option = get_option( $this->token . '_companies' );

		$data = 'Company 1,Company 2';
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		echo '<input id="companies" type="text" name="' . $this->token . '_companies" value="' . $data . '"/>
					<label for="companies"><span class="description">' . sprintf( __( 'Define the companies that job openings will be posted for.', $this->token ) ) . '</span></label>';
	}

	/**
	 * Create the application page field for the Settings page.
	 */
	public function application_page_field()
	{
		$option = get_option( $this->token . '_applyp' );

		$data = '';
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		echo '<input id="applyp" type="text" name="' . $this->token . '_applyp" value="' . $data . '"/>
					<label for="applyp"><span class="description">' . sprintf( __( 'Title of the page containing the [EMAPPLY] shortcode.', $this->token ) ) . '</span></label>';
	}

	/**
	 * Create the auto reply name field for the Settings page.
	 */
	public function auto_reply_name_field()
	{
		$option = get_option( $this->token . '_rname' );

		$data = 'Human Resources';
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		echo '<input id="rname" type="text" name="' . $this->token . '_rname" value="' . $data . '"/>
					<label for="rname"><span class="description">' . sprintf( __( 'The name that emails from the plugin will be sent from.', $this->token ) ) . '</span></label>';
	}

	/**
	 * Create the auto reply content field for the Settings page.
	 */
	public function auto_reply_content_field()
	{
		$option = get_option( $this->token . '_reply' );

		$data = get_option( 'admin_email' );
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		echo '<input id="reply" type="text" name="' . $this->token . '_reply" value="' . $data . '"/>
					<label for="reply"><span class="description">' . sprintf( __( 'Email that will be sent to user when application is submitted.', $this->token ) ) . '</span></label>';
	}

	/**
	 * Create the disclaimer field for the Settings page.
	 */
	public function disclaimer_field()
	{
		$option = get_option( $this->token . '_disclaimer' );

		$data = '';
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		echo '<input id="disclaimer" type="text" name="' . $this->token . '_disclaimer" value="' . $data . '"/>
					<label for="disclaimer"><span class="description">' . sprintf( __( 'Optional disclaimer to be displayed on the application..', $this->token ) ) . '</span></label>';
	}

	/**
	 * Create the actual HTML structure
	 * for the Settings page for the plugin
	 *
	 */
	public function settings_page()
	{
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true ) {
			echo '<div class="updated"><p>Successfully updated.</p></div>';
		}

		echo '<div class="wrap" id="' . $this->token . '_settings">
						<h2>' . __( 'WordPress Job Openings Settings', $this->token ) . '</h2>
						<form method="post" action="options.php" enctype="multipart/form-data">
							<div class="clear"></div>';

		settings_fields( $this->token );
		do_settings_sections( $this->token );

		echo '<p class="submit">
								<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings', $this->token ) ) . '" />
							</p>
						</form>
				  </div>';
	}
}