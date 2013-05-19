<?php
	/*
	Plugin Name: WP Job Openings
	Plugin URI: http://www.lsius.net/
	Description: Creates Simple Way to Manage Job Postings and Employment Information
	Author: Aaron Huisinga
	Version: 0.2
	Author URI: http://www.lsius.net/
	*/
?>
<?php
	
	/* Initailaize Back-end */	
	function wpem_admin_init() {
		$page_title = "WP Job Openings Configuration";
		$menu_title = "Job Openings";
		$capability = "publish_posts";
		$menu_slug = "wpem_config";
		$function = "wpem_config_page";
		$icon_url = "";
		$position = "";
		
		add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function );
	}
	add_action('admin_menu', 'wpem_admin_init');
	
	
	/* Load Default Settings */
	function wpem_default_settings() {
		$tmp = get_option('wpem_options');
		if(!is_array($tmp)) {
			$arr = array(
				'companies' => ''
			);
			update_option('wpem_options', $arr);
		}
	}
	register_activation_hook(__FILE__, 'wpem_default_settings');
	
	
	/* Settings */
	function wpem_settings_init() {
	
		add_settings_section('wpem_id', '', 'wpem_callback', 'wpem_page');
		
		register_setting( 'wpem_optiongroup', 'wpem_options' ); // General Settings
		
		/* Add fields to cover page settings */
		add_settings_field('companies', 'Company Names', 'wpem_setting_string', 'wpem_page', 'wpem_id', array('id' => 'companies', 'type' => 'text') );
	}
	add_action('admin_init', 'wpem_settings_init');
	
		function wpem_callback() { echo '<p>Adjust settings for the employment plugin below. <br> <i>For the companies field, list the names of the different tags that you will give your posts, separated by commas. (Ex. Company1,Company2,...)</i></p>'; }

		function wpem_setting_string( $args ) {
			$options = get_option('wpem_options');
			$id = $args['id'];
			$type = $args['type'];
			
			switch($type) {
				case 'text':
					$class = ($args['class']) ? ' class="'.$args['class'].'"' : '';
					echo "<input id='wpem_".$id."' name='wpem_options[".$id."]' type='text'". $class ." value='".$options[$id]."' />";
					break;
				default:
					break;
			}			
		}
	
	/* Back-end Interface */	
	function wpem_config_page() { ?>
		<div class="wrap">
			<div id="poststuff">
				<?php echo '<h1 class="wpem-title">' . __( 'WP Job Openings Configuration', 'wpem-config' ) . '</h1>'; ?>
				<div class="clear"></div>
				
				<div class="postbox">
					<h3>Employment Settings</h3>
					
					<div class="inside">
						<form method="post" action="options.php">
							<?php settings_fields( 'wpem_optiongroup' ); ?>
							<?php do_settings_sections( 'wpem_page' ); ?>
							<?php submit_button(); ?>
						</form>
					</div>
				</div><!-- #postbox -->
			</div><!-- #poststuff -->
		</div>
	<?php }
	
	/* Register custom post type */
	function wpem_post_type_init() {
		$labels = array(
			'name' => _x('Job Openings', 'post type general name'),
			'singular_name' => _x('Job Opening', 'post type singular name'),
			'add_new' => _x('Add New', 'timeline'),
			'add_new_item' => __('Add New Job Opening'),
			'edit_item' => __('Edit Job Opening'),
			'new_item' => __('New Job Opening'),
			'all_items' => __('All Job Openings'),
			'view_item' => __('View Job Opening'),
			'search_items' => __('Search Job Openings'),
			'not_found' =>  __('No Job Openings found'),
			'not_found_in_trash' => __('No Job Openings found in Trash'), 
			'parent_item_colon' => '',
			'menu_name' => __('Job Openings')
		);
		$args = array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true, 
			'show_in_menu' => true, 
			'query_var' => true,
			'rewrite' => true,
			'capability_type' => 'post',
			'has_archive' => true, 
			'hierarchical' => false,
			'menu_position' => null,
			'taxonomies' => array('post_tag'),
			'supports' => array( 'title', 'editor' ),
			'register_meta_box_cb' => 'wpem_meta_boxes'
		); 
		register_post_type( 'openings' , $args );
		
	}
	add_action( 'init', 'wpem_post_type_init' );
	
	/* Metaboxes for Job Opening Post Type */
	function wpem_meta_boxes() {
		add_meta_box( 'openings-meta', 'Job Opening Details', 'wpem_meta_boxes_inner', 'openings' );
	}

	/* Prints the box content */
	function wpem_meta_boxes_inner() {
		global $post;
		wp_nonce_field( plugin_basename( __FILE__ ), 'wpem_noncename' );
		$meta = get_post_meta($post->ID);
		?>
		<div class="wpem-metabox">
			<div class="wpem-metabox-item">
				<label for="wpem_wage">Wage:</label>
				<input type="text" id="wpem_wage" name="wpemmeta[wpem_wage]" value="<?php echo $meta['wpem_wage'][0]; ?>">
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_hours">Hours:</label>
				<input type="text" id="wpem_hours" name="wpemmeta[wpem_hours]" value="<?php echo $meta['wpem_hours'][0]; ?>">
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_contact">Contact:</label>
				<input type="text" id="wpem_contact" name="wpemmeta[wpem_contact]" value="<?php echo $meta['wpem_contact'][0]; ?>">
			</div>
			
			<input type="submit" class="button" name="wpem_meta_submit" value="Save Job Opening Details">
		</div>
		<?php
	}
	
	
	/* Save Meta Data */
	function wpem_save_wpem_meta($post_id, $post) {
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( !wp_verify_nonce( $_POST['wpem_noncename'], plugin_basename(__FILE__) )) {
			return $post->ID;
		}
		// Is the user allowed to edit the post or page?
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		$wpem_meta = $_POST['wpemmeta'];
		
		foreach ($wpem_meta as $key => $value) {
			if( $post->post_type == 'revision' ) return;
			if(get_post_meta($post->ID, $key, FALSE)) {
				update_post_meta($post->ID, $key, $value);
			} else {
				add_post_meta($post->ID, $key, $value);
			}
			if(!$value) delete_post_meta($post->ID, $key);
		}
	}
	add_action('save_post', 'wpem_save_wpem_meta', 1, 2);
	
	function wpem_func($atts) {
	  $options = get_option('wpem_options');
		$companies = explode(",", $options['companies']);
    
    foreach($companies as $x) {
    	$tag=str_replace(' ', '-', $x);
    	$args=array('post_type' => 'openings', 'tag' => $tag);
    	$my_query = new WP_Query( $args );
    	$i = 0;
    	
    	if($my_query->have_posts()) {
    		echo "<legend>$x</legend>";
        while($i < $my_query->post_count) : 
        	$post = $my_query->posts;
        	$meta = get_post_meta($post[$i]->ID);
        	
        	echo '<table class="table table-striped table-bordered table-condensed">
        				<tr>
        					<td colspan="2"><h4>'.$post[$i]->post_title.'</h4></td>
        				</tr>
        					<td><strong>Wage: </strong>'.$meta['wpem_wage'][0].'</td>
        					<td><strong>Hours: </strong>'.$meta['wpem_hours'][0].'</td>
        				</tr>
        				<tr>
        					<td colspan="2">';
        					echo wpautop($post[$i]->post_content);
        					echo '<hr>
        					<button class="apply btn btn-primary" id="'.$post[$i]->ID.'">Apply Now</button>
        					</td>
        				</tr>
        				</table>';
          
          $post = '';
          $i++;  
        endwhile;
      }
    }
  }
	add_shortcode('WPEM', 'wpem_func');
	
	
?>