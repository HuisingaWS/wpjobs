<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Employment {
	private $dir;
	private $file;
	private $assets_dir;
	private $assets_url;
	private $uploads_dir;
	private $home_url;
	private $token;

	/**
	 * Basic constructor for the WP Employment class
	 *
	 * @param string $file
	 */
	public function __construct( $file )
	{
		$this->dir         = dirname( $file );
		$this->file        = $file;
		$this->assets_dir  = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url  = esc_url( trailingslashit( plugins_url( '/assets/', $file ) ) );
		$this->uploads_dir = wp_upload_dir();
		$this->home_url    = home_url();
		$this->token       = 'wp_employment';

		// Register 'wp_employment' post type
		add_action( 'init', array( $this, 'register_post_type' ) );

		// Register shortcodes
		add_shortcode( 'WPEM', array( $this, 'display_job_listings_page' ) );
		add_shortcode( 'EMAPPLY', array( $this, 'display_job_application_page' ) );

		// Handle form submissions
		add_action( 'wp_ajax_' . $this->token . '_submit_application', [ $this, 'process_application' ] );
		add_action( 'wp_ajax_nopriv_' . $this->token . '_submit_application', [ $this, 'process_application' ] );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'meta_box_setup' ), 20 );
			add_action( 'save_post', array( $this, 'meta_box_save' ) );
		}
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

		$slug        = __( 'openings', $this->token );
		$custom_slug = get_option( $this->token . '_slug' );
		if ( $custom_slug && strlen( $custom_slug ) > 0 && $custom_slug != '' )
			$slug = $custom_slug;

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $slug ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'taxonomies'         => array( 'post_tag' ),
			'supports'           => array( 'title', 'editor' ),
			'menu_position'      => 5
		);

		register_post_type( $this->token, $args );
	}

	/**
	 * Build the meta box containing our custom fields
	 * for our WP Employment post type creator & editor.
	 */
	public function meta_box_setup()
	{
		add_meta_box( $this->token . '-meta', __( 'Job Opening Details', $this->token ), array(
			$this,
			'meta_box_content'
		),
			$this->token, 'normal', 'high'
		);

		do_action( $this->token . '_meta_boxes' );
	}

	/**
	 * Build the custom fields that will be displayed
	 * in the meta box for our WP Employment post type.
	 *
	 * @param $post
	 * @param $meta
	 */
	public function meta_box_content( $post, $meta )
	{
		global $post_id;
		$fields     = get_post_custom( $post_id );
		$field_data = $this->get_custom_fields_settings();

		$html = '';
		$html .= '<input type="hidden" name="' . $this->token . '_nonce" id="' . $this->token . '_nonce" value="' . wp_create_nonce( plugin_basename( $this->dir ) ) . '">';

		if ( 0 < count( $field_data ) ) {
			$html .= '<table class="form-table">' . "\n";
			$html .= '<tbody>' . "\n";

			$html .= '<input id="' . $this->token . '_post_id" type="hidden" value="' . $post_id . '" />';

			foreach ( $field_data as $k => $v ) {
				$data        = $v['default'];
				$placeholder = $v['placeholder'];
				$type        = $v['type'];
				if ( isset( $fields[ $k ] ) && isset( $fields[ $k ][0] ) )
					$data = $fields[ $k ][0];

				if ( $type == 'text' || $type == 'email' ) {
					$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td>';
					$html .= '<input style="width:100%" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '" placeholder="' . esc_attr( $placeholder ) . '" type="' . $type . '" value="' . esc_attr( $data ) . '" />';
					$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
					$html .= '</td><tr/>' . "\n";
				} elseif ( $type == 'url' ) {
					$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td><input type="button" class="button" id="upload_media_file_button" value="' . __( 'Upload Image', $this->token ) . '" data-uploader_title="Choose an image" data-uploader_button_text="Insert image file" /><input name="' . esc_attr( $k ) . '" type="text" id="upload_media_file" class="regular-text" value="' . esc_attr( $data ) . '" />' . "\n";
					$html .= '<p class="description">' . $v['description'] . '</p>' . "\n";
					$html .= '</td><tr/>' . "\n";
				} elseif ( $type == 'select' ) {
					$html .= '<tr valign="top"><th scope="row"><label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label></th><td>';
					$html .= '<select style="width:100%" name="' . esc_attr( $k ) . '" id="' . esc_attr( $k ) . '">';
					foreach ( $v['options'] as $option => $value ) {
						$selected = '';
						if ( esc_attr( $data ) == $value ) {
							$selected = 'selected="selected"';
						}

						$html .= '<option value="' . $option . '" ' . $selected . '>' . $value . '</option>';
					}
					$html .= '</select><p class="description">' . $v['description'] . '</p>' . "\n";
					$html .= '</td><tr/>' . "\n";
				}

				$html .= '</td><tr/>' . "\n";
			}

			$html .= '</tbody>' . "\n";
			$html .= '</table>' . "\n";
		}

		echo $html;
	}

	/**
	 * Save the data entered by the user using
	 * the custom fields for our WP Employment post type.
	 *
	 * @param integer $post_id
	 *
	 * @return int
	 */
	public function meta_box_save( $post_id )
	{
		// Verify
		if ( ( get_post_type() != $this->token ) || ! wp_verify_nonce( $_POST[ $this->token . '_nonce' ], plugin_basename( $this->dir ) ) )
			return $post_id;

		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		$field_data = $this->get_custom_fields_settings( 'all' );
		$fields     = array_keys( $field_data );

		foreach ( $fields as $f ) {
			if ( isset( $_POST[ $f ] ) ) {
				${$f} = strip_tags( trim( $_POST[ $f ] ) );
			}

			// Escape the URLs.
			if ( 'url' == $field_data[ $f ]['type'] ) {
				${$f} = esc_url( ${$f} );
			}

			if ( ${$f} == '' ) {
				delete_post_meta( $post_id, $f, get_post_meta( $post_id, $f, true ) );
			} else {
				update_post_meta( $post_id, $f, ${$f} );
			}
		}
	}

	/**
	 * Define the custom fields that will
	 * be displayed and used for our
	 * WP Employment post type.
	 *
	 * @return mixed
	 */
	public function get_custom_fields_settings()
	{
		$fields    = array();
		$options   = array();
		$companies = explode( ',', get_option( $this->token . '_companies' ) );
		foreach ( $companies as $company ) {
			$options[ $company ] = $company;
		}

		$fields['company'] = array(
			'name'        => __( 'Company', $this->token ),
			'description' => __( 'The company this job is for.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => $options,
			'section'     => 'info'
		);

		$fields['wage'] = array(
			'name'        => __( 'Wage', $this->token ),
			'description' => __( 'The wage to be paid for the job.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'Hourly'     => 'Hourly',
				'Hourly DOQ' => 'Hourly DOQ',
				'Negotiable' => 'Negotiable',
				'Salaried'   => 'Salaried'
			),
			'section'     => 'info'
		);

		$fields['hours'] = array(
			'name'        => __( 'Hours', $this->token ),
			'description' => __( 'The hours required for the job.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'Full Time'                => 'Full Time',
				'Full Time (First Shift)'  => 'Full Time (First Shift)',
				'Full Time (Second Shift)' => 'Full Time (Second Shift)',
				'Full Time 12 Hr Shifts'   => 'Full Time 12 Hr Shifts',
				'Part Time'                => 'Part Time',
				'Part Time (First Shift)'  => 'Part Time (First Shift)',
				'Part Time (Second Shift)' => 'Part Time (Second Shift)'
			),
			'section'     => 'info'
		);

		$fields['contact'] = array(
			'name'        => __( 'Contact Email', $this->token ),
			'description' => __( 'The contact email for the job.', $this->token ),
			'placeholder' => 'example@example.com',
			'type'        => 'email',
			'default'     => get_option( 'admin_email' ),
			'section'     => 'info'
		);

		$fields['resume'] = array(
			'name'        => __( 'Resume Attachment', $this->token ),
			'description' => __( 'Should users be allowed to submit resume files?', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'Yes' => 'Yes',
				'No'  => 'No'
			),
			'section'     => 'info'
		);

		$fields['custom_name'] = array(
			'name'        => __( 'Custom Field Name', $this->token ),
			'description' => __( '(optional) Name for custom field on application form.', $this->token ),
			'placeholder' => '',
			'type'        => 'email',
			'default'     => get_option( 'admin_email' ),
			'section'     => 'info'
		);

		$fields['custom_type'] = array(
			'name'        => __( 'Custom Field Type', $this->token ),
			'description' => __( '(optional) Type of the custom field on application form.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'text'     => 'Text',
				'textarea' => 'Textarea'
			),
			'section'     => 'info'
		);

		$fields['education'] = array(
			'name'        => __( 'Display Education History', $this->token ),
			'description' => __( 'Display fields related to education history on application form.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'Yes' => 'Yes',
				'No'  => 'No'
			),
			'section'     => 'info'
		);

		$fields['military'] = array(
			'name'        => __( 'Display Military History', $this->token ),
			'description' => __( 'Display fields related to military history on application form.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'Yes' => 'Yes',
				'No'  => 'No'
			),
			'section'     => 'info'
		);

		$fields['previous'] = array(
			'name'        => __( 'Display Previous Employment', $this->token ),
			'description' => __( 'Display fields related to employment history on application form.', $this->token ),
			'placeholder' => '',
			'type'        => 'select',
			'options'     => array(
				'Yes' => 'Yes',
				'No'  => 'No'
			),
			'section'     => 'info'
		);

		return apply_filters( $this->token . '_meta_fields', $fields );
	}

	/**
	 * Process the application and notify
	 * the contact for the job opening.
	 */
	public function process_application()
	{
		die( var_dump( $_POST ) );
	}

	/**
	 * Register the Javascript files that will be
	 * used for our templates.
	 *
	 * @param $page
	 */
	public function enqueue_scripts( $page )
	{
		if ( $page == 'listings' ) {
			wp_register_script( $this->token . '-js', esc_url( $this->assets_url . 'js/wp-employment.js' ), array(
				'jquery'
			) );
			wp_enqueue_script( $this->token . '-js' );
		} elseif ( $page == 'application' ) {
			wp_register_script( $this->token . '-js', esc_url( $this->assets_url . 'js/wp-employment-application.js' ), array(
				'jquery'
			) );
			wp_enqueue_script( $this->token . '-js' );
		}

		wp_register_style( $this->token, esc_url( $this->assets_url . 'css/wp-employment.css' ), array() );
		wp_enqueue_style( $this->token );

		$localize = [
			'ajaxurl' => admin_url( 'admin-ajax.php' )
		];
		wp_localize_script( $this->token . '-js', 'WPEmployment', $localize );
	}

	protected function get_apply_page()
	{
		global $wpdb;

		return $wpdb->get_var( "SELECT post_name FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' AND post_content LIKE '%[EMAPPLY]%' LIMIT 1" );
	}

	/**
	 * Render the Job Listings page when
	 * the WPEM shortcode is used.
	 */
	public function display_job_listings_page()
	{
		$this->enqueue_scripts( 'listings' );
		$companies        = explode( ',', get_option( $this->token . '_companies' ) );
		$args             = array( 'post_type' => $this->token, 'orderby' => 'title', 'order' => 'ASC' );
		$openings         = new WP_Query( $args );
		$application_page = $this->get_apply_page();

		if ( $openings->have_posts() ) {
			foreach ( $companies as $company ) {
				echo '<h3>' . $company . '</h3>';
				foreach ( $openings as $key => $opening ) {
					$meta = get_post_meta( $opening->ID );
					if ( $meta['company'][0] != $company ) {
						continue;
					}
					?>
					<table class="table wp-employment-table">
						<tr>
							<td colspan="3"><h4><?php echo $opening->post_title; ?></h4></td>
						</tr>
						<tr>
							<td width="33%"><strong>Wage: </strong><?php echo $meta['wage'][0]; ?></td>
							<td width="33%"><strong>Hours: </strong><?php echo $meta['hours'][0]; ?></td>
							<td width="33%"><strong>Details: </strong>
								<a href="#" class="wp-employment-more" id="<?php echo $opening->ID; ?>">Show</a></td>
						</tr>
						<tr class="<?php echo $opening->ID; ?> wp-employment-details">
							<td colspan="3">
								<?php echo wpautop( $opening->post_content ); ?>
								<hr>
								<a class="btn wp-employment-btn" href="<?php echo $this->home_url . '/' . $application_page . '/?pos=' . $opening->ID; ?>">Apply
									Now</a>
							</td>
						</tr>
					</table>
				<?php
				}
			}
		}
	}

	/**
	 * Render the Job Application page when
	 * the EMAPPLY shortcode is used.
	 */
	public function display_job_application_page()
	{
		$this->enqueue_scripts( 'application' );
		$pid     = $_GET['pos'];
		$post    = get_post( $pid );
		$title   = $post->post_title;
		$meta    = get_post_meta( $pid );
		$options = get_option( $this->token . '_options' );

		?>
		<div id="wp-employment-apply">
			<form id="wp-employment-form" action="<?php echo admin_url( 'admin-ajax.php' ); ?>" method="post">
				<input type="hidden" id="wp-employment-title" name="title" value="<?php echo $title; ?>">
				<input type="hidden" id="wp-employment-contact" name="contact" value="<?php echo $meta['contact'][0]; ?>">
				<input name="action" value="wp_employment_submit_application" type="hidden">
				<?php wp_nonce_field( $this->token . '_submit_application', $this->token . '_nonce' ); ?>

				<h2>General Information</h2>
				<table class="table wp-employment-apply-table">
					<tr>
						<td>
							<label for="first" class="wp-employment-label">First Name</label>
							<input type="text" id="first" name="first" placeholder="Ex: John">
						</td>
						<td>
							<label for="last" class="wp-employment-label">Last Name</label>
							<input type="text" id="last" name="last" placeholder="Ex: Smith">
						</td>
					</tr>
					<tr>
						<td>
							<label for="email" class="wp-employment-label">Email Address</label>
							<input type="email" id="email" name="email" placeholder="Ex: yourname@example.com">
						</td>
						<td>
							<label for="phone" class="wp-employment-label">Phone Number</label>
							<input type="text" id="phone" name="phone" placeholder="Ex: (555) 555-5555">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="address" class="wp-employment-label">Mailing Address</label>
							<textarea id="address" rows="3" name="address" placeholder="Ex: 123 1st St, Willmar, MN 56201"></textarea>
						</td>
					</tr>
					<?php

					if ( strlen( $meta['custom'][0] ) > 1 && strlen( $meta['custom2'][0] ) > 1 ) {
						echo '<tr><td colspan="2"><label class="wp-employment-label" for="' . $meta['custom'][0] . '">' . $meta['custom'][0] . '</label>';
						if ( $meta['custom2'][0] == 'text' ) {
							echo '<input type="text" id="' . $meta['custom'][0] . '" name="' . $meta['custom'][0] . '">';
						} else {
							echo '<textarea rows="5" id="' . $meta['custom'][0] . '" name="' . $meta['custom'][0] . '"></textarea>';
						}
						echo '</td></tr>';
					}

					?>
				</table>

				<h2>Disclaimer and Signature</h2>

				<p><em><?php echo wpautop( $options['disclaimer'] ); ?></em></p>
				<table class="table wp-employment-table">
					<tr>
						<td>
							<label for="signature" class="wp-employment-label">Signature</label>
							<input type="text" id="signature" name="signature" placeholder="Ex: John Smith">
						</td>
					</tr>
				</table>

				<?php if ( $meta['resume'][0] == 'Yes' ) { ?>
					<h2>Resume & Cover Letter</h2>
					<input id="wp-employment-resume-file" name="wp-employment-resume-file" type="file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
					<strong>If you include a resume, completion of the fields below is completely optional.</strong><br>
				<?php } ?>

				<br>

				<h2>Further Information</h2>
				<table class="table wp-employment-table">
					<tr>
						<td>
							<label for="available" class="wp-employment-label">Date Available</label>
							<input type="text" id="available" name="available">
						</td>
						<td>
							<label for="salary" class="wp-employment-label">Desired Salary</label>
							<input type="text" id="salary" name="salary">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="experience" class="wp-employment-label">Experience & Knowledge for this Position</label>
							<textarea id="experience" rows="5" name="experience"></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<label for="skills" class="wp-employment-label">Special Skills Applicable to Position</label>
							<textarea id="skills" rows="5" name="skills"></textarea>
						</td>
					</tr>
					<tr>
						<td>
							<label for="citizen" class="wp-employment-label">Are you a citizen of the United States?</label>
							<select name="citizen" id="citizen">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</td>
						<td>
							<label for="authorized" class="wp-employment-label">If no, are you authorized to work in the U.S.?</label>
							<select name="authorized" id="authorized">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label for="relocate" class="wp-employment-label">Would you be willing to relocate?</label>
							<select name="relocate" id="relocate">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</td>
						<td>
							<label for="relocate2" class="wp-employment-label">If yes, explain</label>
							<input type="text" id="relocate2" name="relocate2">
						</td>
					</tr>
					<tr>
						<td>
							<label for="previous" class="wp-employment-label">Have you worked for one or more of our companies
								before?</label>
							<select name="previous" id="previous">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</td>
						<td>
							<label for="previous2" class="wp-employment-label">If so, which and when?</label>
							<input type="text" id="previous2" name="previous2">
						</td>
					</tr>
					<tr>
						<td>
							<label for="felony" class="wp-employment-label">Have you ever been convicted of a felony?</label>
							<select name="felony" id="felony">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</td>
						<td>
							<label for="felony2" class="wp-employment-label">If yes, explain</label>
							<input type="text" id="felony2" name="felony2">
						</td>
					</tr>
				</table>

				<?php if ( isset( $meta['education'][0] ) && $meta['education'][0] == 'Yes' ) { ?>
					<h2>Education History</h2>
					<table class="table wp-employment-table">
						<tr>
							<td colspan="3">
								<label for="hs" class="wp-employment-label">High School</label>
								<input type="text" id="hs" name="hs" placeholder="Ex: Willmar Senior High School">
							</td>
							<td colspan="3">
								<label for="hs2" class="wp-employment-label">City, State</label>
								<input type="text" id="hs2" name="hs2" placeholder="Ex: Willmar, MN">
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<label for="hs3" class="wp-employment-label">From</label>
								<input type="text" id="hs3" name="hs3" placeholder="Ex: 2008">
							</td>
							<td colspan="2">
								<label for="hs4" class="wp-employment-label">To</label>
								<input type="text" id="hs4" name="hs4" placeholder="Ex: 2012">
							</td>
							<td colspan="2">
								<label for="hs5" class="wp-employment-label">Did you graduate?</label>
								<select name="hs5" id="hs5">
									<option></option>
									<option value="Yes">YES</option>
									<option value="No">NO</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="6"></td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="c11" class="wp-employment-label">College</label>
								<input type="text" id="c11" name="c11" placeholder="Ex: University of Minnesota">
							</td>
							<td colspan="3">
								<label for="c12" class="wp-employment-label">City, State</label>
								<input type="text" id="c12" name="c12" placeholder="Ex: Minneapolis, MN">
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="c13" class="wp-employment-label">From</label>
								<input type="text" id="c13" name="c13" placeholder="Ex: 2008">
							</td>
							<td colspan="3">
								<label for="c14" class="wp-employment-label">To</label>
								<input type="text" id="c14" name="c14" placeholder="Ex: 2012">
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="c15" class="wp-employment-label">Did you graduate?</label>
								<select name="c15" id="c15">
									<option></option>
									<option value="Yes">YES</option>
									<option value="No">NO</option>
								</select>
							</td>
							<td colspan="3">
								<label for="c16" class="wp-employment-label">Degree</label>
								<input type="text" id="c16" name="c16" placeholder="Ex: BS in Computer Science">
							</td>
						</tr>
						<tr>
							<td colspan="6"></td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="c21" class="wp-employment-label">College</label>
								<input type="text" id="c21" name="c21" placeholder="Ex: University of Minnesota">
							</td>
							<td colspan="3">
								<label for="c22" class="wp-employment-label">City, State</label>
								<input type="text" id="c22" name="c22" placeholder="Ex: Minneapolis, MN">
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="c23" class="wp-employment-label">From</label>
								<input type="text" id="c23" name="c23" placeholder="Ex: 2008">
							</td>
							<td colspan="3">
								<label for="c24" class="wp-employment-label">To</label>
								<input type="text" id="c24" name="c24" placeholder="Ex: 2012">
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="c25" class="wp-employment-label">Did you graduate?</label>
								<select name="c25" id="c25">
									<option></option>
									<option value="Yes">YES</option>
									<option value="No">NO</option>
								</select>
							</td>
							<td colspan="3">
								<label for="c26" class="wp-employment-label">Degree</label>
								<input type="text" id="c26" name="c26" placeholder="Ex: BS in Computer Science">
							</td>
						</tr>
						<tr>
							<td colspan="6"></td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="objectives" class="wp-employment-label">Briefly Describe Your Career Objectives</label>
								<textarea id="objectives" rows="5" name="objectives"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="etc" class="wp-employment-label">Special Training, Experience, or Pertinent Data</label>
								<textarea id="etc" rows="5" name="etc"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="referral" class="wp-employment-label">How Did You Hear About Us?</label>
								<textarea id="referral" rows="5" name="referral"></textarea>
							</td>
						</tr>
					</table>
				<?php } ?>

				<?php if ( isset( $meta['military'][0] ) && $meta['military'][0] == 'Yes' ) { ?>
					<h2>Military Service</h2>
					<table class="table wp-employment-table">
						<tr>
							<td colspan="6">
								<label for="branch" class="wp-employment-label">Branch</label>
								<input type="text" id="branch" name="branch">
							</td>
							<td colspan="3">
								<label for="mi1" class="wp-employment-label">From</label>
								<input type="text" id="mi1" name="mi1">
							</td>
							<td colspan="3">
								<label for="mi2" class="wp-employment-label">To</label>
								<input type="text" id="mi2" name="mi2">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="mi3" class="wp-employment-label">Rank at Discharge</label>
								<input type="text" id="mi3" name="mi3">
							</td>
							<td colspan="6">
								<label for="mi4" class="wp-employment-label">Type of Discharge</label>
								<input type="text" id="mi4" name="mi4">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								<label for="mi5" class="wp-employment-label">If Other than Honorable, Explain</label>
								<textarea id="mi5" rows="3" name="mi5"></textarea>
							</td>
						</tr>
					</table>
				<?php } ?>

				<?php if ( isset( $meta['previous'][0] ) && $meta['previous'][0] == 'Yes' ) { ?>
					<h2>Previous Employment</h2>
					<em>List Present or Most Recent Employer First</em>

					<table class="table wp-employment-app-table">
						<tr>
							<td colspan="6">
								<label for="peco1" class="wp-employment-label">Company</label>
								<input type="text" id="peco1" name="pec1">
							</td>
							<td colspan="6">
								<label for="pead1" class="wp-employment-label">Address</label>
								<input type="text" id="pead1" name="pead1">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pesu1" class="wp-employment-label">Supervisor</label>
								<input type="text" id="pesu1" name="pesu1">
							</td>
							<td colspan="6">
								<label for="peph1" class="wp-employment-label">Phone</label>
								<input type="text" id="peph1" name="peph1">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pejt1" class="wp-employment-label">Job Title</label>
								<input type="text" id="pejt1" name="pejt1">
							</td>
							<td colspan="3">
								<label for="pess1" class="wp-employment-label">Starting Salary</label>
								<input type="text" id="pess1" name="pess1">
							</td>
							<td colspan="3">
								<label for="pees1" class="wp-employment-label">Ending Salary</label>
								<input type="text" id="pees1" name="pees1">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								<label for="peres1" class="wp-employment-label">Responsibilities</label>
								<textarea id="peres1" rows="5" name="peres1"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="pefr1" class="wp-employment-label">From</label>
								<input type="text" id="pefr1" name="pefr1">
							</td>
							<td colspan="3">
								<label for="peto1" class="wp-employment-label">To</label>
								<input type="text" id="peto1" name="peto1">
							</td>
							<td colspan="6">
								<label for="perl1" class="wp-employment-label">Reason for Leaving</label>
								<input type="text" id="perl1" name="perl1">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								May we contact your previous supervisor for a reference?
								<label for="peref1" class="radio"><input type="radio" name="peref1" id="peref1" value="yes"> Yes</label>
								<label for="peref1" class="radio"><input type="radio" name="peref1" id="peref1" value="no"> No</label>
							</td>
						</tr>
						<tr>
							<td colspan="12"></td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="peco2" class="wp-employment-label">Company</label>
								<input type="text" id="peco2" name="pec2">
							</td>
							<td colspan="6">
								<label for="pead2" class="wp-employment-label">Address</label>
								<input type="text" id="pead2" name="pead2">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pesu2" class="wp-employment-label">Supervisor</label>
								<input type="text" id="pesu2" name="pesu2">
							</td>
							<td colspan="6">
								<label for="peph2" class="wp-employment-label">Phone</label>
								<input type="text" id="peph2" name="peph2">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pejt2" class="wp-employment-label">Job Title</label>
								<input type="text" id="pejt2" name="pejt2">
							</td>
							<td colspan="3">
								<label for="pess2" class="wp-employment-label">Starting Salary</label>
								<input type="text" id="pess2" name="pess2">
							</td>
							<td colspan="3">
								<label for="pees2" class="wp-employment-label">Ending Salary</label>
								<input type="text" id="pees2" name="pees2">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								<label for="peres2" class="wp-employment-label">Responsibilities</label>
								<textarea id="peres2" rows="5" name="peres2"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="pefr2" class="wp-employment-label">From</label>
								<input type="text" id="pefr2" name="pefr2">
							</td>
							<td colspan="3">
								<label for="peto2" class="wp-employment-label">To</label>
								<input type="text" id="peto2" name="peto2">
							</td>
							<td colspan="6">
								<label for="perl2" class="wp-employment-label">Reason for Leaving</label>
								<input type="text" id="perl2" name="perl2">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								May we contact your previous supervisor for a reference?
								<label for="peref2" class="radio"><input type="radio" name="peref2" id="peref2" value="yes"> Yes</label>
								<label for="peref2" class="radio"><input type="radio" name="peref2" id="peref2" value="no"> No</label>
							</td>
						</tr>
						<tr>
							<td colspan="12"></td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="peco3" class="wp-employment-label">Company</label>
								<input type="text" id="peco3" name="pec3">
							</td>
							<td colspan="6">
								<label for="pead3" class="wp-employment-label">Address</label>
								<input type="text" id="pead3" name="pead3">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pesu3" class="wp-employment-label">Supervisor</label>
								<input type="text" id="pesu3" name="pesu3">
							</td>
							<td colspan="6">
								<label for="peph3" class="wp-employment-label">Phone</label>
								<input type="text" id="peph3" name="peph3">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pejt3" class="wp-employment-label">Job Title</label>
								<input type="text" id="pejt3" name="pejt3">
							</td>
							<td colspan="3">
								<label for="pess3" class="wp-employment-label">Starting Salary</label>
								<input type="text" id="pess3" name="pess3">
							</td>
							<td colspan="3">
								<label for="pees3" class="wp-employment-label">Ending Salary</label>
								<input type="text" id="pees3" name="pees3">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								<label for="peres3" class="wp-employment-label">Responsibilities</label>
								<textarea id="peres3" rows="5" name="peres3"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="pefr3" class="wp-employment-label">From</label>
								<input type="text" id="pefr3" name="pefr3">
							</td>
							<td colspan="3">
								<label for="peto3" class="wp-employment-label">To</label>
								<input type="text" id="peto3" name="peto3">
							</td>
							<td colspan="6">
								<label for="perl3" class="wp-employment-label">Reason for Leaving</label>
								<input type="text" id="perl3" name="perl3">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								May we contact your previous supervisor for a reference?
								<label for="peref3" class="radio"><input type="radio" name="peref3" id="peref3" value="yes"> Yes</label>
								<label for="peref3" class="radio"><input type="radio" name="peref3" id="peref3" value="no"> No</label>
							</td>
						</tr>
						<tr>
							<td colspan="12"></td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="peco4" class="wp-employment-label">Company</label>
								<input type="text" id="peco4" name="pec4">
							</td>
							<td colspan="6">
								<label for="pead4" class="wp-employment-label">Address</label>
								<input type="text" id="pead4" name="pead4">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pesu4" class="wp-employment-label">Supervisor</label>
								<input type="text" id="pesu4" name="pesu4">
							</td>
							<td colspan="6">
								<label for="peph4" class="wp-employment-label">Phone</label>
								<input type="text" id="peph4" name="peph4">
							</td>
						</tr>
						<tr>
							<td colspan="6">
								<label for="pejt4" class="wp-employment-label">Job Title</label>
								<input type="text" id="pejt4" name="pejt4">
							</td>
							<td colspan="3">
								<label for="pess4" class="wp-employment-label">Starting Salary</label>
								<input type="text" id="pess4" name="pess4">
							</td>
							<td colspan="3">
								<label for="pees4" class="wp-employment-label">Ending Salary</label>
								<input type="text" id="pees4" name="pees4">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								<label for="peres4" class="wp-employment-label">Responsibilities</label>
								<textarea id="peres4" rows="5" name="peres4"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<label for="pefr4" class="wp-employment-label">From</label>
								<input type="text" id="pefr4" name="pefr4">
							</td>
							<td colspan="3">
								<label for="peto4" class="wp-employment-label">To</label>
								<input type="text" id="peto4" name="peto4">
							</td>
							<td colspan="6">
								<label for="perl4" class="wp-employment-label">Reason for Leaving</label>
								<input type="text" id="perl4" name="perl4">
							</td>
						</tr>
						<tr>
							<td colspan="12">
								May we contact your previous supervisor for a reference?
								<label for="peref4" class="radio"><input type="radio" name="peref4" id="peref4" value="yes"> Yes</label>
								<label for="peref4" class="radio"><input type="radio" name="peref4" id="peref4" value="no"> No</label>
							</td>
						</tr>
					</table>
				<?php } ?>

				<hr>

				<button type="submit" class="btn wp-employment-btn btn-success" name="submit">Submit Application</button>
			</form>
		</div>
	<?php
	}
}