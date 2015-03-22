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
			'companies'
		), $this->token, 'customize', array(
			'id'          => 'companies',
			'type'        => 'text',
			'placeholder' => 'Company 1,Company 2'
		) );

		add_settings_field( $this->token . '_applyp', __( 'Application Page', $this->token ), array(
			$this,
			'applyp'
		), $this->token, 'customize', array(
			'id'          => $this->token . '_applyp',
			'type'        => 'text',
			'placeholder' => 'Title of the page containing the [EMAPPLY] shortcode'
		) );

		add_settings_field( $this->token . '_rname', __( 'Auto Reply From (Name)', $this->token ), array(
			$this,
			'rname'
		), $this->token, 'customize', array(
			'id'          => $this->token . '_rname',
			'type'        => 'text',
			'placeholder' => 'Human Resources'
		) );

		add_settings_field( $this->token . '_reply', __( 'Auto Reply Content', $this->token ), array(
			$this,
			'reply'
		), $this->token, 'customize', array(
			'id'          => $this->token . '_reply',
			'type'        => 'textarea',
			'placeholder' => 'Email that will be sent to user when application is submitted'
		) );

		add_settings_field( $this->token . '_disclaimer', __( 'Application Disclaimer', $this->token ), array(
			$this,
			'disclaimer'
		), $this->token, 'customize', array(
			'id'          => $this->token . '_disclaimer',
			'type'        => 'textarea',
			'placeholder' => 'Optional disclaimer to be displayed on the application'
		) );

		// Register settings fields
		register_setting( $this->token, $this->token . '_companies' );
		register_setting( $this->token, $this->token . '_applyp' );
		register_setting( $this->token, $this->token . '_rname' );
		register_setting( $this->token, $this->token . '_reply' );
		register_setting( $this->token, $this->token . '_disclaimer' );

		// Allow plugins to add more settings fields
		do_action( $this->token . '_settings_fields' );
	}
}