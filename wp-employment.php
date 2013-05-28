<?php
	/*
	Plugin Name: WP Job Openings
	Plugin URI: https://github.com/ahuisinga/wpjobs
	Description: Creates Simple Way to Manage Job Postings and Employment Information
	Author: Aaron Huisinga
	Version: 0.4
	Author URI: https://github.com/ahuisinga
	*/
?>
<?php
	define( 'PLUGIN_PATH', plugin_dir_url(__FILE__) );
	define( 'ATTACH_PATH', plugin_dir_path(__FILE__) );
	
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
		add_settings_field('rname', 'Auto Reply From (Name)', 'wpem_setting_string', 'wpem_page', 'wpem_id', array('id' => 'rname', 'type' => 'text') );
		add_settings_field('reply', 'Auto Reply Content', 'wpem_setting_string', 'wpem_page', 'wpem_id', array('id' => 'reply', 'type' => 'textarea') );
		add_settings_field('disclaimer', 'Application Disclaimer', 'wpem_setting_string', 'wpem_page', 'wpem_id', array('id' => 'disclaimer', 'type' => 'textarea') );
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
					echo "<input style='width: 90%;' id='wpem_".$id."' name='wpem_options[".$id."]' type='text'". $class ." value='".$options[$id]."' />";
					break;
				case 'textarea':
					$class = ($args['class']) ? ' class="'.$args['class'].'"' : '';
					echo "<textarea style='width: 90%;' rows='15' id='wpem_".$id."' name='wpem_options[".$id."]' ". $class .">".$options[$id]."</textarea>";
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
			<h4>General Job Details</h4>
			<div class="wpem-metabox-item">
				<label for="wpem_wage">Wage:</label>
				<select id="wpem_wage" name="wpemmeta[wpem_wage]">
					<option value=""></option>
					<option value="Hourly" <? if($meta['wpem_wage'][0] == "Hourly"){ echo "selected"; } ?>>Hourly</option>
					<option value="Hourly DOQ" <? if($meta['wpem_wage'][0] == "Hourly DOQ"){ echo "selected"; } ?>>Hourly DOQ</option>
					<option value="Negotiable" <? if($meta['wpem_wage'][0] == "Negotiable"){ echo "selected"; } ?>>Negotiable</option>
					<option value="Salaried" <? if($meta['wpem_wage'][0] == "Salaried"){ echo "selected"; } ?>>Salaried</option>
				</select>
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_hours">Hours:</label>
				<select id="wpem_hours" name="wpemmeta[wpem_hours]">
					<option value=""></option>
					<option value="Full-Time" <? if($meta['wpem_hours'][0] == "Full-Time"){ echo "selected"; } ?>>Full-Time</option>
					<option value="Full-Time (First Shift)" <? if($meta['wpem_hours'][0] == "Full-Time (First Shift)"){ echo "selected"; } ?>>Full-Time (First Shift)</option>
					<option value="Full-Time (Second Shift)" <? if($meta['wpem_hours'][0] == "Full-Time (Second Shift)"){ echo "selected"; } ?>>Full-Time (Second Shift)</option>
					<option value="Full-Time 12 Hr Shifts" <? if($meta['wpem_hours'][0] == "Full-Time 12 Hr Shifts"){ echo "selected"; } ?>>Full-Time 12 Hr Shifts</option>
					<option value="Part-Time" <? if($meta['wpem_hours'][0] == "Part-Time"){ echo "selected"; } ?>>Part-Time</option>
					<option value="Part-Time (First Shift)" <? if($meta['wpem_hours'][0] == "Part-Time (First Shift)"){ echo "selected"; } ?>>Part-Time (First Shift)</option>
					<option value="Part-Time (Second Shift)" <? if($meta['wpem_hours'][0] == "Part-Time (Second Shift)"){ echo "selected"; } ?>>Part-Time (Second Shift)</option>
				</select>
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_contact">Contact:</label>
				<input type="text" style="width: 70%;" id="wpem_contact" name="wpemmeta[wpem_contact]" value="<?php echo $meta['wpem_contact'][0]; ?>">
			</div>
			
			<h4>Job Application Details</h4>
			<div class="wpem-metabox-item">
				<label for="wpem_resume">Resume Attachment:</label>
				<select id="wpem_resume" name="wpemmeta[wpem_resume]">
					<option value="Yes" <? if($meta['wpem_resume'][0] == "Yes"){ echo "selected"; } ?>>Yes</option>
					<option value="No" <? if($meta['wpem_resume'][0] == "No"){ echo "selected"; } ?>>No</option>
				</select>
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_custom">Custom Field Name (optional):</label>
				<input type="text" style="width: 70%;" id="wpem_custom" name="wpemmeta[wpem_custom]" value="<?php echo $meta['wpem_custom'][0]; ?>">
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_custom2">Custom Field Type (optional):</label>
				<select id="wpem_custom2" name="wpemmeta[wpem_custom2]">
					<option value=""></option>
					<option value="text" <? if($meta['wpem_custom2'][0] == "text"){ echo "selected"; } ?>>Text</option>
					<option value="textarea" <? if($meta['wpem_custom2'][0] == "textarea"){ echo "selected"; } ?>>Textarea</option>
				</select>
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_edu">Display Education History:</label>
				<select id="wpem_edu" name="wpemmeta[wpem_edu]">
					<option value="Yes" <? if($meta['wpem_edu'][0] == "Yes"){ echo "selected"; } ?>>Yes</option>
					<option value="No" <? if($meta['wpem_edu'][0] == "No"){ echo "selected"; } ?>>No</option>
				</select>
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_mil">Display Military Service:</label>
				<select id="wpem_mil" name="wpemmeta[wpem_mil]">
					<option value="Yes" <? if($meta['wpem_mil'][0] == "Yes"){ echo "selected"; } ?>>Yes</option>
					<option value="No" <? if($meta['wpem_mil'][0] == "No"){ echo "selected"; } ?>>No</option>
				</select>
			</div>
			<div class="wpem-metabox-item">
				<label for="wpem_pem">Display Previous Employment:</label>
				<select id="wpem_pem" name="wpemmeta[wpem_pem]">
					<option value="Yes" <? if($meta['wpem_pem'][0] == "Yes"){ echo "selected"; } ?>>Yes</option>
					<option value="No" <? if($meta['wpem_pem'][0] == "No"){ echo "selected"; } ?>>No</option>
				</select>
			</div>
			<br>
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
	
	// Display Functions and Short Codes
	// Job Listings Page
	function wpem_func($atts) {
		$url = home_url();
	  $options = get_option('wpem_options');
		$companies = explode(",", $options['companies']);
    
    foreach($companies as $x) {
    	$tag=str_replace(' ', '-', $x);
    	$args=array('post_type' => 'openings', 'tag' => $tag, 'orderby' => 'title', 'order' => 'ASC');
    	$my_query = new WP_Query( $args );
    	$i = 0;
    	
    	if($my_query->have_posts()) {
    		echo "<legend>$x</legend>
    					<div class=\"well well-small\">";
        while($i < $my_query->post_count) : 
        	$post = $my_query->posts;
        	$meta = get_post_meta($post[$i]->ID);
        	
        	echo '<table class="table table-striped table-bordered table-condensed">
        				<tr>
        					<td colspan="3"><h4>'.$post[$i]->post_title.'</h4></td>
        				</tr>
        					<td width="33%"><strong>Wage: </strong>'.$meta['wpem_wage'][0].'</td>
        					<td width="33%"><strong>Hours: </strong>'.$meta['wpem_hours'][0].'</td>
        					<td width="33%"><strong>Details: </strong> <a href="#" class="more" id="'.$post[$i]->ID.'">Show</a></td>
        				</tr>
        				<tr class="'.$post[$i]->ID.' jdetails">
        					<td colspan="3">';
        						echo wpautop($post[$i]->post_content);
        						echo '<hr>
        						<center><a class="btn btn-primary" href="'.$url.'/apply/?pos='.$post[$i]->ID.'"><i class="icon-inbox"></i> Apply Now</a></center>
        					</td>
        				</tr>
        				</table>';
          
          $post = '';
          $i++;  
        endwhile;
        echo "</div>";
      }
    }
    echo '<script>
    				$(document).ready(function () {
    					$(".jdetails").hide();
    					$(".more").click(function () {
    						if($(this).text() != "Hide") {
    							$(".jdetails").hide("4000");
    							$(".more").text("Show");
    							var toggle = $(this).attr("id");
    							$(this).text("Hide");
    							$("."+toggle).show("4000", function() {
    								$(this).parent().parent()[0].scrollIntoView(true);
    							});
    						} else {
    							$(".jdetails").hide("4000");
    							$(".more").text("Show");
    						}
    						return false;
    					});
    				});
    			</script>';
  }
	add_shortcode('WPEM', 'wpem_func');
	
	// Application Page
	function wpem_apply($atts) {
  	preg_match_all('!\d+!', $_SERVER["REQUEST_URI"], $pid);
  	$pid = implode(' ', $pid[0]);
    $post = get_post($pid); 
		$title = $post->post_title;
		$meta = get_post_meta($pid);
		// Fixes the paths for Windows
		$workaround = str_replace("\\", "|", ATTACH_PATH);
		// Query the automatic email reply content
		$options = get_option('wpem_options');
		$reply = $options['reply'];
		$rname = $options['rname'];
		
		
		//echo "<h2 id='title'>$title Application</h2>";
		
		echo '<span id="apply">
					<legend>General Information</legend>
					<div class="row">
						<div class="span6">
							<label for="first" class="text-info">First Name</label>
							<input type="text" id="first" class="span6" name="first" placeholder="Ex: John">
						</div>
						<div class="span6">
							<label for="last" class="text-info">Last Name</label>
							<input type="text" id="last" class="span6" name="last" placeholder="Ex: Smith">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="email" class="text-info">Email Address</label>
							<input type="text" id="email" class="span6" name="email" placeholder="Ex: yourname@example.com">
						</div>
						<div class="span6">
							<label for="phone" class="text-info">Phone Number</label>
							<input type="text" id="phone" class="span6" name="phone" placeholder="Ex: (555) 555-5555">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="address" class="text-info">Mailing Address</label>
							<textarea id="address" class="span12" rows="3" name="address" placeholder="Ex: 123 1st St, Willmar, MN 56201"></textarea>
						</div>
					</div>';
					
					if(strlen($meta['wpem_custom'][0]) > 1) {
						echo '<div class="row"><div class="span12"><label class="text-info" for="'.$meta['wpem_custom'][0].'">'.$meta['wpem_custom'][0].'</label>';
						if($meta['wpem_custom2'][0] == 'text') {
							echo '<input type="text" class="span12" id="'.$meta['wpem_custom'][0].'" name="'.$meta['wpem_custom'][0].'">';
						} else {
							echo '<textarea class="span12" rows="5" id="'.$meta['wpem_custom'][0].'" name="'.$meta['wpem_custom'][0].'"></textarea>';
						}
						echo "</div></div>";
					}
					
		echo '<br><br>
					<legend>Disclaimer and Signature</legend>
					<p><em>'.wpautop($options['disclaimer']).'</em></p>
					<div class="row">
						<div class="span12">
							<label for="signature" class="text-info">Signature</label>
							<input type="text" id="signature" class="span12" name="signature" placeholder="Ex: John Smith">
						</div>
					</div>';
					
			if($meta['wpem_resume'][0] == 'Yes') {
				echo '<br><br>
							<legend>Resume & Cover Letter</legend>
							<form id="resumeform">
							<button class="btn btn-primary disabled" disabled="disabled" id="resume"><i class="icon-cloud-upload"></i> Attach Resume</button>
							<input id="resumefile" name="resumefile" type="file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" width="20">
							</form>
							<strong>If you include a resume, completion of the fields below is completely optional.</strong> <br><br>';
				// Code for the resume uploader
				echo '<script type="text/javascript">
			   	 		$(document).ready(function () {
			   	 			$("#resumefile").change(function () {
			   	 				if($(this).val() !== "") {
			   	 					$("#resume").removeClass("disabled");
			   	 					$("#resume").removeAttr("disabled");
			   	 				} else {
			   	 					$("#resume").addClass("disabled");
			   	 					$("#resume").attr("disabled","disabled");
			   	 				}
			   	 			});
					 	 		$("#resume").click(function () {
			            var iframe = $(\'<iframe name="postiframe" id="postiframe" style="display: none" />\');
			            $("body").append(iframe);
			            var form = $("#resumeform");
			            form.attr("action", "'.PLUGIN_PATH . 'resume.php");
			            form.attr("method", "post");
			            form.attr("enctype", "multipart/form-data");
			            form.attr("encoding", "multipart/form-data");
			            form.attr("target", "postiframe");
			            form.attr("file", $("#resumefile").val());
			            form.submit();
			
			            $("#postiframe").load(function () {
			            	iframeContents = $("#postiframe")[0].contentWindow.document.body.innerHTML;
			              $("#resattach").val(iframeContents);
			              $("#resume").addClass("disabled");
			   	 					$("#resume").attr("disabled","disabled");
			   	 					$("#resume").html("<i class=\"icon-cloud-upload\"></i> Resume Uploaded!");
			   	 					$("#resumefile").hide();
			            });
			            return false;
								});
							});
							</script>';
			}			
					
		echo '<br><br>
					<legend>Further Information</legend>
					<div class="row">
						<div class="span6">
							<label for="available">Date Available</label>
							<input type="text" id="available" class="span6" name="available">
						</div>
						<div class="span6">
							<label for="salary">Desired Salary</label>
							<input type="text" id="salary" class="span6" name="salary">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="experience">Experience & Knowledge for this Position</label>
							<textarea id="experience" class="span12" rows="5" name="experience"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="skills">Special Skills Applicable to Position</label>
							<textarea id="skills" class="span12" rows="5" name="skills"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="citizen">Are you a citizen of the United States?</label>
							<select name="citizen" id="citizen" class="span6">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
						<div class="span6">
							<label for="authorized">If no, are you authorized to work in the U.S.?</label>
							<select name="authorized" id="authorized" class="span6">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="relocate">Would you be willing to relocate?</label>
							<select name="relocate" id="relocate" class="span6">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
						<div class="span6">
							<label for="relocate2">If yes, explain</label>
							<input type="text" id="relocate2" class="span6" name="relocate2">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="previous">Have you worked for one or more of our companies before?</label>
							<select name="previous" id="previous" class="span6">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
						<div class="span6">
							<label for="previous2">If so, which and when?</label>
							<input type="text" id="previous2" class="span6" name="previous2">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="felony">Have you ever been convicted of a felony?</label>
							<select name="felony" id="felony" class="span6">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
						<div class="span6">
							<label for="felony2">If yes, explain</label>
							<input type="text" id="felony2" class="span6" name="felony2">
						</div>
					</div>';
					
					if(isset($meta['wpem_edu'][0]) && $meta['wpem_edu'][0] == 'Yes') {
					echo '
					<br><br>
					<legend>Education History</legend>
					<div class="row">
						<div class="span6">
							<label for="hs">High School</label>
							<input type="text" id="hs" class="span6" name="hs" placeholder="Ex: Willmar Senior High School">
						</div>
						<div class="span6">
							<label for="hs2">City, State</label>
							<input type="text" id="hs2" class="span6" name="hs2" placeholder="Ex: Willmar, MN">
						</div>
					</div>
					<div class="row">
						<div class="span4">
							<label for="hs3">From</label>
							<input type="text" id="hs3" class="span4" name="hs3" placeholder="Ex: 2008">
						</div>
						<div class="span4">
							<label for="hs4">To</label>
							<input type="text" id="hs4" class="span4" name="hs4" placeholder="Ex: 2012">
						</div>
						<div class="span4">
							<label for="hs5">Did you graduate?</label>
							<select name="hs5" id="hs5" class="span4">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="span6">
							<label for="c11">College</label>
							<input type="text" id="c11" class="span6" name="c11" placeholder="Ex: University of Minnesota">
						</div>
						<div class="span6">
							<label for="c12">City, State</label>
							<input type="text" id="c12" class="span6" name="c12" placeholder="Ex: Minneapolis, MN">
						</div>
					</div>
					<div class="row">
						<div class="span3">
							<label for="c13">From</label>
							<input type="text" id="c13" class="span3" name="c13" placeholder="Ex: 2008">
						</div>
						<div class="span3">
							<label for="c14">To</label>
							<input type="text" id="c14" class="span3" name="c14" placeholder="Ex: 2012">
						</div>
						<div class="span3">
							<label for="c15">Did you graduate?</label>
							<select name="c15" id="c15" class="span3">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
						<div class="span3">
							<label for="c16">Degree</label>
							<input type="text" id="c16" class="span3" name="c16" placeholder="Ex: BS in Computer Science">
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="span6">
							<label for="c21">College</label>
							<input type="text" id="c21" class="span6" name="c21" placeholder="Ex: University of Minnesota">
						</div>
						<div class="span6">
							<label for="c22">City, State</label>
							<input type="text" id="c22" class="span6" name="c22" placeholder="Ex: Minneapolis, MN">
						</div>
					</div>
					<div class="row">
						<div class="span3">
							<label for="c23">From</label>
							<input type="text" id="c23" class="span3" name="c23" placeholder="Ex: 2008">
						</div>
						<div class="span3">
							<label for="c24">To</label>
							<input type="text" id="c24" class="span3" name="c24" placeholder="Ex: 2012">
						</div>
						<div class="span3">
							<label for="c25">Did you graduate?</label>
							<select name="c25" id="c25" class="span3">
								<option></option>
								<option value="Yes">YES</option>
								<option value="No">NO</option>
							</select>
						</div>
						<div class="span3">
							<label for="c26">Degree</label>
							<input type="text" id="c26" class="span3" name="c26" placeholder="Ex: BS in Computer Science">
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="span12">
							<label for="objectives">Briefly Describe Your Career Objectives</label>
							<textarea id="objectives" class="span12" rows="5" name="objectives"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="etc">Special Training, Experience, or Pertinent Data</label>
							<textarea id="etc" class="span12" rows="5" name="etc"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="referral">How Did You Hear About Us?</label>
							<textarea id="referral" class="span12" rows="5" name="referral"></textarea>
						</div>
					</div>';
					}
					
					if(isset($meta['wpem_mil'][0]) && $meta['wpem_mil'][0] == 'Yes') {
					echo '
					<br><br>
					<legend>Military Service</legend>
					<div class="row">
						<div class="span6">
							<label for="branch">Branch</label>
							<input type="text" id="branch" class="span6" name="branch">
						</div>
						<div class="span3">
							<label for="mi1">From</label>
							<input type="text" id="mi1" class="span3" name="mi1">
						</div>
						<div class="span3">
							<label for="mi2">To</label>
							<input type="text" id="mi2" class="span3" name="mi2">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="mi3">Rank at Discharge</label>
							<input type="text" id="mi3" class="span6" name="mi3">
						</div>
						<div class="span6">
							<label for="mi4">Type of Discharge</label>
							<input type="text" id="mi4" class="span6" name="mi4">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="mi5">If Other than Honorable, Explain</label>
							<textarea id="mi5" class="span12" rows="3" name="mi5"></textarea>
						</div>
					</div>';
					}
					
					if(isset($meta['wpem_pem'][0]) && $meta['wpem_pem'][0] == 'Yes') {
					echo '
					<br><br>
					<legend>Previous Employment</legend>
					<em>List Present or Most Recent Employer First</em>
					<br><br>
					<div class="row">
						<div class="span6">
							<label for="peco1">Company</label>
							<input type="text" id="peco1" class="span6" name="pec1">
						</div>
						<div class="span6">
							<label for="pead1">Address</label>
							<input type="text" id="pead1" class="span6" name="pead1">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pesu1">Supervisor</label>
							<input type="text" id="pesu1" class="span6" name="pesu1">
						</div>
						<div class="span6">
							<label for="peph1">Phone</label>
							<input type="text" id="peph1" class="span6" name="peph1">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pejt1">Job Title</label>
							<input type="text" id="pejt1" class="span6" name="pejt1">
						</div>
						<div class="span3">
							<label for="pess1">Starting Salary</label>
							<input type="text" id="pess1" class="span3" name="pess1">
						</div>
						<div class="span3">
							<label for="pees1">Ending Salary</label>
							<input type="text" id="pees1" class="span3" name="pees1">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="peres1">Responsibilities</label>
							<textarea id="peres1" class="span12" rows="5" name="peres1"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span3">
							<label for="pefr1">From</label>
							<input type="text" id="pefr1" class="span3" name="pefr1">
						</div>
						<div class="span3">
							<label for="peto1">To</label>
							<input type="text" id="peto1" class="span3" name="peto1">
						</div>
						<div class="span6">
							<label for="perl1">Reason for Leaving</label>
							<input type="text" id="perl1" class="span6" name="perl1">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							May we contact your previous supervisor for a reference?
							<label for="peref1" class="radio">
								Yes
								<input type="radio" name="peref1" id="peref1" value="yes">
							</label>
							<label for="peref1" class="radio">
								No
								<input type="radio" name="peref1" id="peref1" value="no">
							</label>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="span6">
							<label for="peco2">Company</label>
							<input type="text" id="peco2" class="span6" name="pec2">
						</div>
						<div class="span6">
							<label for="pead2">Address</label>
							<input type="text" id="pead2" class="span6" name="pead2">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pesu2">Supervisor</label>
							<input type="text" id="pesu2" class="span6" name="pesu2">
						</div>
						<div class="span6">
							<label for="peph2">Phone</label>
							<input type="text" id="peph2" class="span6" name="peph2">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pejt2">Job Title</label>
							<input type="text" id="pejt2" class="span6" name="pejt2">
						</div>
						<div class="span3">
							<label for="pess2">Starting Salary</label>
							<input type="text" id="pess2" class="span3" name="pess2">
						</div>
						<div class="span3">
							<label for="pees2">Ending Salary</label>
							<input type="text" id="pees2" class="span3" name="pees2">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="peres2">Responsibilities</label>
							<textarea id="peres2" class="span12" rows="5" name="peres2"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span3">
							<label for="pefr2">From</label>
							<input type="text" id="pefr2" class="span3" name="pefr2">
						</div>
						<div class="span3">
							<label for="peto2">To</label>
							<input type="text" id="peto2" class="span3" name="peto2">
						</div>
						<div class="span6">
							<label for="perl2">Reason for Leaving</label>
							<input type="text" id="perl2" class="span6" name="perl2">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							May we contact your previous supervisor for a reference?
							<label for="peref2" class="radio">
								Yes
								<input type="radio" name="peref2" id="peref2" value="yes">
							</label>
							<label for="peref2" class="radio">
								No
								<input type="radio" name="peref2" id="peref2" value="no">
							</label>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="span6">
							<label for="peco3">Company</label>
							<input type="text" id="peco3" class="span6" name="pec3">
						</div>
						<div class="span6">
							<label for="pead3">Address</label>
							<input type="text" id="pead3" class="span6" name="pead3">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pesu3">Supervisor</label>
							<input type="text" id="pesu3" class="span6" name="pesu3">
						</div>
						<div class="span6">
							<label for="peph3">Phone</label>
							<input type="text" id="peph3" class="span6" name="peph3">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pejt3">Job Title</label>
							<input type="text" id="pejt3" class="span6" name="pejt3">
						</div>
						<div class="span3">
							<label for="pess3">Starting Salary</label>
							<input type="text" id="pess3" class="span3" name="pess3">
						</div>
						<div class="span3">
							<label for="pees3">Ending Salary</label>
							<input type="text" id="pees3" class="span3" name="pees3">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="peres3">Responsibilities</label>
							<textarea id="peres3" class="span12" rows="5" name="peres3"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span3">
							<label for="pefr3">From</label>
							<input type="text" id="pefr3" class="span3" name="pefr3">
						</div>
						<div class="span3">
							<label for="peto3">To</label>
							<input type="text" id="peto3" class="span3" name="peto3">
						</div>
						<div class="span6">
							<label for="perl3">Reason for Leaving</label>
							<input type="text" id="perl3" class="span6" name="perl3">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							May we contact your previous supervisor for a reference?
							<label for="peref3" class="radio">
								Yes
								<input type="radio" name="peref3" id="peref3" value="yes">
							</label>
							<label for="peref3" class="radio">
								No
								<input type="radio" name="peref3" id="peref3" value="no">
							</label>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="span6">
							<label for="peco4">Company</label>
							<input type="text" id="peco4" class="span6" name="pec4">
						</div>
						<div class="span6">
							<label for="pead4">Address</label>
							<input type="text" id="pead4" class="span6" name="pead4">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pesu4">Supervisor</label>
							<input type="text" id="pesu4" class="span6" name="pesu4">
						</div>
						<div class="span6">
							<label for="peph4">Phone</label>
							<input type="text" id="peph4" class="span6" name="peph4">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="pejt4">Job Title</label>
							<input type="text" id="pejt4" class="span6" name="pejt4">
						</div>
						<div class="span3">
							<label for="pess4">Starting Salary</label>
							<input type="text" id="pess4" class="span3" name="pess4">
						</div>
						<div class="span3">
							<label for="pees4">Ending Salary</label>
							<input type="text" id="pees4" class="span3" name="pees4">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="peres4">Responsibilities</label>
							<textarea id="peres4" class="span12" rows="5" name="peres4"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span3">
							<label for="pefr4">From</label>
							<input type="text" id="pefr4" class="span3" name="pefr4">
						</div>
						<div class="span3">
							<label for="peto4">To</label>
							<input type="text" id="peto4" class="span3" name="peto4">
						</div>
						<div class="span6">
							<label for="perl4">Reason for Leaving</label>
							<input type="text" id="perl4" class="span6" name="perl4">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							May we contact your previous supervisor for a reference?
							<label for="peref4" class="radio">
								Yes
								<input type="radio" name="peref4" id="peref4" value="yes">
							</label>
							<label for="peref4" class="radio">
								No
								<input type="radio" name="peref4" id="peref4" value="no">
							</label>
						</div>
					</div>';
					}
		if(strlen($reply) > 0) {
			echo '<input type="hidden" id="reply" name="reply" value="'.$reply.'">';
		}
		echo "<hr>";
		echo '<button type="submit" class="btn btn-success" name="submit" id="submit"><i class="icon-ok"></i> Submit Application</button>
					<input type="hidden" id="resattach" name="resattach">
					</span>';
		echo '<script type="text/javascript">
		   	 		$(document).ready(function () {
		   	 			$("#submit").click(function () {
		   	 				var address = $("#address").val().replace(/\r\n|\r|\n/g,"<br>");
		   	 						experience = $("#experience").val().replace(/\r\n|\r|\n/g,"<br>");
		   	 						skills = $("#skills").val().replace(/\r\n|\r|\n/g,"<br>");
		   	 						reply = $("#reply").val().replace(/\r\n|\r|\n/g,"<br>");
		   	 						
			   	 			$.ajax({
				 	 				url: "'.PLUGIN_PATH . 'resume.php",
				 	 				data: {"pdir" : "'.$workaround.'",
				 	 							 "jobtitle" : "'.$title.'",
				 	 							 "contact" : "'.$meta['wpem_contact'][0].'",
				 	 							 "resattach" : $("#resattach").val(),';
				 	 							 if(strlen($meta['wpem_custom'][0]) > 1) {
				 	 				  echo '"custom1" : "'.$meta['wpem_custom'][0].'",
				 	 							  "custom2" : $("[id=\''.$meta['wpem_custom'][0].'\']").val(),';
				 	 							 }
				 	 							 if(strlen($reply) > 1) {
				 	 				  echo '"reply" : reply,
				 	 				  			"rname" : "'.$rname.'",';
				 	 							 }
				 	 					if(isset($meta['wpem_edu'][0]) && $meta['wpem_edu'][0] == 'Yes') {
				 	 					echo '"hs" : $("#hs").val(),
				 	 								"hs2" : $("#hs2").val(),
				 	 								"hs3" : $("#hs3").val(),
				 	 								"hs4" : $("#hs4").val(),
				 	 								"hs5" : $("#hs5").val(),
				 	 								"c11" : $("#c11").val(),
				 	 								"c12" : $("#c12").val(),
				 	 								"c13" : $("#c13").val(),
				 	 								"c14" : $("#c14").val(),
				 	 								"c15" : $("#c15").val(),
				 	 								"c16" : $("#c15").val(),
				 	 								"c21" : $("#c21").val(),
				 	 								"c22" : $("#c22").val(),
				 	 								"c23" : $("#c23").val(),
				 	 								"c24" : $("#c24").val(),
				 	 								"c25" : $("#c25").val(),
				 	 								"c26" : $("#c25").val(),
				 	 								"objectives" : $("#objectives").val(),
				 	 								"etc" : $("#etc").val(),
				 	 								"referral" : $("#referral").val(),';
				 	 					}
				 	 					if(isset($meta['wpem_mil'][0]) && $meta['wpem_mil'][0] == 'Yes') {
				 	 					echo '"branch" : $("#branch").val(),
				 	 								"mi1" : $("#mi1").val(),
				 	 								"mi2" : $("#mi2").val(),
				 	 								"mi3" : $("#mi3").val(),
				 	 								"mi4" : $("#mi4").val(),
				 	 								"mi5" : $("#mi5").val(),
				 	 							 ';
				 	 					}
				 	 					if(isset($meta['wpem_pem'][0]) && $meta['wpem_pem'][0] == 'Yes') {
				 	 					echo '"peco1" : $("#peco1").val(),
				 	 								"pead1" : $("#pead1").val(),
				 	 								"pesu1" : $("#pesu1").val(),
				 	 								"peph1" : $("#peph1").val(),
				 	 								"pejt1" : $("#pejt1").val(),
				 	 								"pess1" : $("#pess1").val(),
				 	 								"pees1" : $("#pees1").val(),
				 	 								"peres1" : $("#peres1").val(),
				 	 								"pefr1" : $("#pefr1").val(),
				 	 								"peto1" : $("#peto1").val(),
				 	 								"perl1" : $("#perl1").val(),
				 	 								"peref1" : $("#peref1").val(),
				 	 								"peco2" : $("#peco2").val(),
				 	 								"pead2" : $("#pead2").val(),
				 	 								"pesu2" : $("#pesu2").val(),
				 	 								"peph2" : $("#peph2").val(),
				 	 								"pejt2" : $("#pejt2").val(),
				 	 								"pess2" : $("#pess2").val(),
				 	 								"pees2" : $("#pees2").val(),
				 	 								"peres2" : $("#peres2").val(),
				 	 								"pefr2" : $("#pefr2").val(),
				 	 								"peto2" : $("#peto2").val(),
				 	 								"perl2" : $("#perl2").val(),
				 	 								"peref2" : $("#peref2").val(),
				 	 								"peco3" : $("#peco3").val(),
				 	 								"pead3" : $("#pead3").val(),
				 	 								"pesu3" : $("#pesu3").val(),
				 	 								"peph3" : $("#peph3").val(),
				 	 								"pejt3" : $("#pejt3").val(),
				 	 								"pess3" : $("#pess3").val(),
				 	 								"pees3" : $("#pees3").val(),
				 	 								"peres3" : $("#peres3").val(),
				 	 								"pefr3" : $("#pefr3").val(),
				 	 								"peto3" : $("#peto3").val(),
				 	 								"perl3" : $("#perl3").val(),
				 	 								"peref3" : $("#peref3").val(),
				 	 								"peco4" : $("#peco4").val(),
				 	 								"pead4" : $("#pead4").val(),
				 	 								"pesu4" : $("#pesu4").val(),
				 	 								"peph4" : $("#peph4").val(),
				 	 								"pejt4" : $("#pejt4").val(),
				 	 								"pess4" : $("#pess4").val(),
				 	 								"pees4" : $("#pees4").val(),
				 	 								"peres4" : $("#peres4").val(),
				 	 								"pefr4" : $("#pefr4").val(),
				 	 								"peto4" : $("#peto4").val(),
				 	 								"perl4" : $("#perl4").val(),
				 	 								"peref4" : $("#peref4").val(),';
				 	 					}
				 	 					echo '"first" : $("#first").val(),
				 	 							 "last" : $("#last").val(),
				 	 							 "email" : $("#email").val(),
				 	 							 "phone" : $("#phone").val(),
				 	 							 "address" : address,
				 	 							 "skills" : skills,
				 	 							 "available" : $("#available").val(),
				 	 							 "salary" : $("#salary").val(),
				 	 							 "experience" : experience,
				 	 							 "citizen" : $("#citizen").val(),
				 	 							 "authorized" : $("#authorized").val(),
				 	 							 "relocate" : $("#relocate").val(),
				 	 							 "relocate2" : $("#relocate2").val(),
				 	 							 "previous" : $("#previous").val(),
				 	 							 "previous2" : $("#previous2").val(),
				 	 							 "felony" : $("#felony").val(),
				 	 							 "felony2" : $("#felony2").val()
				 	 							},
				 	 				type: "POST",
				 	 				async: false,
				 	 				success:  function(html){
				 	 					$("#apply").before(html);
				 	 					$("#apply").remove();
				 	 					$("#resumeform").remove();
				 	 				}
				 	 			});
				 	 			return false;
				 	 		});
		   	 		});
		   	 	</script>';
  }
	add_shortcode('EMAPPLY', 'wpem_apply');
	
?>