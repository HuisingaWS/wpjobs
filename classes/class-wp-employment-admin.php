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
		add_options_page( 'WP Job Openings Configuration', 'Job Openings', 'publish_posts', $this->token . '_config', array(
			$this,
			'settings_page'
		) );
	}

	/**
	 * Display admin notice about shortcodes after plugin activation
	 */
	public function admin_notice()
	{
		global $current_user;
		$user_id = $current_user->ID;

		if ( ! get_user_meta( $user_id, $this->token . '_ignore_notice' ) ) {
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

		if ( isset( $_GET[ $this->token . '_ignore_notice' ] ) && $_GET[ $this->token . '_ignore_notice' ] == '1' ) {
			add_user_meta( $user_id, $this->token . '_ignore_notice', 'true', true );
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

		add_settings_field( $this->token . '_rname', __( 'Auto Reply From (Name)', $this->token ), array(
			$this,
			'auto_reply_name_field'
		), $this->token, 'customize' );

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
		?>
		<p><?php echo __( 'Adjust settings for the employment plugin below.', $this->token ); ?>
			<br>
			<em><?php echo __( 'For the companies field, list the names of the different tags that you will give your posts, separated by commas. (Ex. Company1,Company2)', $this->token ) ?></em>
		</p>
	<?php
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

		?>
		<input name="<?php echo $this->token . '_companies'; ?>" type="text" id="companies" style="width:80%" value="<?php echo $data; ?>" class="regular-text">
		<p class="description"><?php echo sprintf( __( 'Define the companies that job openings will be posted for.', $this->token ) ); ?></p>
	<?php
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

		?>
		<input name="<?php echo $this->token . '_rname'; ?>" type="text" id="rname" style="width:80%" value="<?php echo $data; ?>" class="regular-text">
		<p class="description"><?php echo sprintf( __( 'The name that emails from the plugin will be sent from.', $this->token ) ); ?></p>
	<?php
	}

	/**
	 * Create the auto reply content field for the Settings page.
	 */
	public function auto_reply_content_field()
	{
		$option = get_option( $this->token . '_reply' );

		$data = "Hello,\nThank you for your application and interest in our position. You are receiving this email to acknowledge our receipt of your employment inquiry. Please be assured that we will review any and all attachments to your submission, and that we will contact you if we experience any problems viewing a document.\n\nWe truly appreciate your time and interest in gaining employment with one of our affiliated companies, but we are limited in our ability to respond to every inquiry. We will contact you if we decide your qualifications match our needs. Please feel free to continue to apply for any position for which you meet the qualifications.\n\nThank you again for your application and have a great day.";
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		?>
		<textarea name="<?php echo $this->token . '_reply'; ?>" id="reply" style="width:80%" rows="10"><?php echo $data; ?></textarea>
		<p class="description"><?php echo sprintf( __( 'Email that will be sent to user when application is submitted.', $this->token ) ); ?></p>
	<?php
	}

	/**
	 * Create the disclaimer field for the Settings page.
	 */
	public function disclaimer_field()
	{
		$option = get_option( $this->token . '_disclaimer' );

		$data = "I certify the information contained in this application is true and complete to the best of my knowledge. I understand that any falsification or omission of information will be sufficient grounds for denial of employment, or if hired, dismissal.\n\nI affirm that I have a genuine intent and no other purposes in applying for a job with this employer.";
		if ( $option && strlen( $option ) > 0 && $option != '' )
			$data = $option;

		?>
		<textarea name="<?php echo $this->token . '_disclaimer'; ?>" id="reply" style="width:80%" rows="10"><?php echo $data; ?></textarea>
		<p class="description"><?php echo sprintf( __( 'Optional disclaimer to be displayed on the application.', $this->token ) ); ?></p>
	<?php
	}

	/**
	 * Create the actual HTML structure
	 * for the Settings page for the plugin
	 *
	 */
	public function settings_page()
	{
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