<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Employment {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $token;

	/**
	 * Basic constructor for the WP Employment class
	 *
	 * @param string $file
	 */
	public function __construct( $file )
	{
		$this->dir        = dirname( $file );
		$this->file       = $file;
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $file ) ) );
		$this->token      = 'wp_employment';

		// Register 'wp_employment' post type
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Registers the House Hunter custom post type
	 * with WordPress, used for our pages.
	 *
	 */
	public function register_post_type()
	{
		$labels = array(
			'name'               => _x( 'Job Openings', 'post type general name', $this->token ),
			'singular_name'      => _x( 'Job Opening', 'post type singular name', $this->token ),
			'add_new'            => _x( 'Add New', $this->token, $this->token ),
			'add_new_item'       => sprintf( __( 'Add New %s', $this->token ), __( 'Job Opening', $this->token ) ),
			'edit_item'          => sprintf( __( 'Edit %s', $this->token ), __( 'Job Opening', $this->token ) ),
			'new_item'           => sprintf( __( 'New %s', $this->token ), __( 'Job Opening', $this->token ) ),
			'all_items'          => sprintf( __( 'All %s', $this->token ), __( 'Job Openings', $this->token ) ),
			'view_item'          => sprintf( __( 'View %s', $this->token ), __( 'Job Opening', $this->token ) ),
			'search_items'       => sprintf( __( 'Search %a', $this->token ), __( 'Job Openings', $this->token ) ),
			'not_found'          => sprintf( __( 'No %s Found', $this->token ), __( 'Job Openings', $this->token ) ),
			'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', $this->token ), __( 'Job Openings', $this->token ) ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Job Openings', $this->token )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => true,
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'taxonomies'         => array( 'post_tag' ),
			'supports'           => array( 'title', 'editor' ),
			'menu_position'      => 5
		);

		register_post_type( $this->token, $args );
	}
}