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
					<option value="Full-Time 12 Hr Shifts" <? if($meta['wpem_hours'][0] == "Full-Time 12 Hr Shifts"){ echo "selected"; } ?>>Full-Time 12 Hr Shifts</option>
					<option value="Part-Time" <? if($meta['wpem_hours'][0] == "Part-Time"){ echo "selected"; } ?>>Part-Time</option>
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
		
		echo "<legend>$title Application</legend>";
		
		echo '<form id="apply" method="POST">
					<div class="row">
						<div class="span6">
							<label for="first">First Name</label>
							<input type="text" id="first" class="span6" name="first" placeholder="Ex: John">
						</div>
						<div class="span6">
							<label for="last">Last Name</label>
							<input type="text" id="last" class="span6" name="last" placeholder="Ex: Smith">
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<label for="email">Email Address</label>
							<input type="text" id="email" class="span6" name="email" placeholder="Ex: yourname@example.com">
						</div>
						<div class="span6">
							<label for="phone">Phone Number</label>
							<input type="text" id="phone" class="span6" name="phone" placeholder="Ex: (555) 555-5555">
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="address">Mailing Address</label>
							<textarea id="address" class="span12" rows="3" name="address" placeholder="Ex: 123 1st St, Willmar, MN 56201"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="education">Education History</label>
							<textarea id="education" class="span12" rows="5" name="education" placeholder="Ex: University of Minnesota - BS in Computer Science - 2012"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="span12">
							<label for="skills">Skills & Certifications</label>
							<textarea id="skills" class="span12" rows="5" name="skills" placeholder="Ex: Microsoft Certified Professional"></textarea>
						</div>
					</div>';
		if(strlen($meta['wpem_custom'][0]) > 1) {
			echo '<div class="row"><div class="span12"><label for="'.$meta['wpem_custom'][0].'">'.$meta['wpem_custom'][0].'</label>';
			if($meta['wpem_custom2'][0] == 'text') {
				echo '<input type="text" class="span12" id="'.$meta['wpem_custom'][0].'" name="'.$meta['wpem_custom'][0].'">';
			} else {
				echo '<textarea class="span12" rows="5" id="'.$meta['wpem_custom'][0].'" name="'.$meta['wpem_custom'][0].'"></textarea>';
			}
			echo "</div></div>";
		}
		if(strlen($reply) > 0) {
			echo '<input type="hidden" id="reply" name="reply" value="'.$reply.'">';
		}
		echo "<hr>";
		echo '<button type="submit" class="btn btn-success" name="submit" id="submit"><i class="icon-ok"></i> Submit Application</button>
					<input type="hidden" id="resattach" name="resattach">
					</form>';
		if($meta['wpem_resume'][0] == 'Yes') {
			echo '<form id="resumeform">
						<button class="btn btn-primary disabled" disabled="disabled" id="resume"><i class="icon-cloud-upload"></i> Attach Resume</button>
						<input id="resumefile" name="resumefile" type="file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" width="20">
						</form>';
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
		echo '<script type="text/javascript">
		   	 		$(document).ready(function () {
		   	 			$("#apply").submit(function () {
		   	 				var address = $("#address").val().replace(/\r\n|\r|\n/g,"<br>");
		   	 						education = $("#education").val().replace(/\r\n|\r|\n/g,"<br>");
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
				 	 					echo '"first" : $("#first").val(),
				 	 							 "last" : $("#last").val(),
				 	 							 "email" : $("#email").val(),
				 	 							 "phone" : $("#phone").val(),
				 	 							 "address" : address,
				 	 							 "education" : education,
				 	 							 "skills" : skills
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