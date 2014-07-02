<?php
	//class for our plug-in logic
	if(!class_exists('GMAPS_LOCATOR') && class_exists('GMAPS_LOCATOR_Options')) {
		class GMAPS_LOCATOR extends GMAPS_LOCATOR_Options {
			private $table_map;
			private $settings;
			public function __construct() {
				parent::__construct();
				$this->get_settings();
				$this->map_tables();

				//locator admin/settings page
				add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
				$this->locator_admin();
				//locator functions
				$this->locator_shortcode_enqueue();
				$this->locator_posttype_tax();
			}

			//our plug-in activation
			public function activate() {
				//call methods to initialize plug-in functionality
				$this->set_options();
				$this->set_tables();
				$this->add_caps();
			}

			//our plug-in deactivation
			public function deactivate() {
				//call methods to remove capabilities
				//we don't remove the tables or options here, they are removed in uninstall.php
				$this->remove_caps();
			}

			//our plug-in uninstall
			public function uninstall() {
				//call methods to remove tables and unset options
				//other plugin data should have been removed on deactivation
				$this->unset_options();
				$this->unset_tables();
			}

			//get current options
			public function get_settings() {
				//get options, use defaults from plugin-options.php if they aren't found
				$opts = get_option($this->fix_name('options'), $this->options->opts[$this->fix_name('options')]);
				//decode the JSON string into an array and save it to $this->current
				if(is_string($opts)) {
					$this->settings = json_decode($opts, true);
				} else {
					$this->settings = $opts;
				}
			}

			//add capabilities
			private function add_caps() {
				//get roles object
				global $wp_roles;
				//iterate through all roles and add the capabilities
				foreach($wp_roles->role_names as $role => $info) {
					//get the role
					$role_obj = get_role($role);
					//iterate through capabilities in the options
					//this gives us an array of capabilities and the capability they require
					foreach($this->caps as $req => $caps) {
						//iterate through our capabilities
						foreach($caps as $key => $cap) {
							//if this role has the required capability
							//but not the capability we want to add
							if(!$role_obj->has_cap($cap) && $role_obj->has_cap($req)) {
								//add capability
								$role_obj->add_cap($cap, true);
							}
						}
					}
				}
			}

			//remove capabilities
			private function remove_caps() {
				//get roles object
				global $wp_roles;
				//iterate through all roles and remove the capabilities
				foreach($wp_roles->roles as $role => $info) {
					//get the role
					$role_obj = get_role($role);
					//iterate through capabilities in the options
					//this gives us an array of capabilities and the capability they require
					foreach($this->caps as $req => $caps) {
						//iterate through our capabilities
						foreach($caps as $key => $cap) {
							//if this role has our capability
							if($role_obj->has_cap($cap)) {
								//remove the capability
								$role_obj->remove_cap($cap);
							}
						}
					}
				}
			}

			private function map_tables() {
				//loop through tables and store them as an array of slug => table_name for easy reference in other methods
				foreach($this->tables as $slug => $sql) {
					//now we can refer to our tables as $this->table_map['slug'];
					$this->table_map[$slug] = $this->fix_name($slug, true);
				}
			}

			//this method creates any necessary tables
			private function set_tables() {
				//loop through each table
				foreach($this->tables as $slug => $sql) {
					//check to see if we need to create the table
					$this->check_DB($this->fix_name($slug, true), $sql);
				}
			}

			//this method checks to make sure tables don't exist before trying to create them
			private function check_DB($table, $sql) {
				//if we can't find the table
				if($this->db->get_var("show tables like '". $table . "'") != $table) {
					//run the table's CREATE statement
					$this->db->query($sql);
				}
			}

			//this method removes tables from the DB
			private function unset_tables() {
				foreach($this->tables as $slug => $sql) {
					$this->db->query("DROP table `" . $this->fix_name($slug, true) . "`");
				}
			}

			//this method sets any necessary options
			private function set_options() {
				//iterate through our options
				foreach($this->options as $name => $val) {
					//if this is our options array
					if($name == $this->fix_name('options')) {
						//iterate through each value
						foreach($val as $key => $value) {
							//check it against the current settings
							if($this->settings[$key] != $value) {
								//if the setting was different, store the current setting, not our default
								$val[$key] = $this->settings[$key];
							}
						}
						//json encode our options array into a string
						$val = json_encode($val);
					}
					//run the option through our update method
					$this->update_option($name, $val);
				}
			}

			//this method removes any necessary options
			public function unset_options() {
				//iterate through our options
				foreach($this->options as $name => $val) {
					//remove the option
					delete_option($name);
				}
			}

			//this method allows us to run some checks when updating versions and changing options
			private function update_option($option, $value) {
				//if the option exists
				if($curr_value = get_option($option)) {
					//if the current value isn't what we want
					if($curr_value !== $value) {
						//check with the pre_update_option method which lets us perform any necessary actions when updating our options
						if($this->pre_update_option($option, $curr_value, $value)) {
							//update the option value
							update_option($option, $value);
						}
					}
				//if it doesn't add it
				} else {
					add_option($option, $value);
				}
			}

			//this method performs checks against specific option names to run update functions prior to saving the option
			private function pre_update_option($name, $old, $new) {
				//we'll make this true when the option is safe to update
				$good_to_go = false;
				//if this is our version number
				if($name === $this->options[$this->fix_name('version')]) {
					//IMPORTANT: call necessary update functions for each version here
					$good_to_go = true;
				//add other elseif branches based on other option updates that might require custom update functionality here
				//otherwise
				} else {
					//if we've got some values in there, we're good
					if($old || $new) {
						$good_to_go = true;
					}
				}
				return $good_to_go;
			}


			//admin scripts and styles
			public function locator_admin(){
				function locator_admin_scripts($hook_suffix) {
				  wp_enqueue_style( 'GMAP_LOCATOR-css-admin',GMAPS_LOCATOR_URL . '/assets/css/GMAPS_LOCATOR-admin.css');
					if($hook_suffix == 'settings_page_gmaps-locator'){
						wp_enqueue_script( 'GMAP_LOCATOR-google', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyA71cJRCCMCcn_VsXSj4wSF4HTX8GyqD24');
						wp_enqueue_script( 'GMAP_LOCATOR-js-admin', GMAPS_LOCATOR_URL . '/assets/js/GMAPS_LOCATOR-admin.js', array('jquery'));
						$settings = get_option( 'locator_options' );
						if(!$settings['gmaps_latitude']){
								$settings['gmaps_latitude'] = 61.1917528;
						}
						if(!$settings['gmaps_longitude']){
								$settings['gmaps_longitude'] = -149.8598051;
						}
						if(!$settings['zoom_level']){
								$settings['zoom_level'] = 13;
						}
						$args = array(
							'latitude'  => $settings['gmaps_latitude'],
							'longitude' => $settings['gmaps_longitude'],
							'zoom'      => $settings['zoom_level']
						);
						wp_localize_script( 'GMAP_LOCATOR-js-admin', 'GMAPS_LOCATOR_data', $args );
					}
				}
				add_action( 'admin_enqueue_scripts', 'locator_admin_scripts' );
			}

			//admin options page
	    public function add_plugin_page(){
	        add_options_page(
	            'Settings Admin',
	            'GMaps Locator',
	            'manage_options',
	            'gmaps-locator',
	            array( $this, 'create_admin_page' )
	        );
	    }
      //admin options page content
	    public function create_admin_page(){
	        // Set class property
	        $this->settings = get_option( 'locator_options' );
	        ?>
	        <div class="wrap">
	            <?php screen_icon(); ?>
	            <h2>Google Maps Locator Configurator</h2>
	            <form method="post" action="options.php">
	            <?php
	                // This prints out all hidden setting fields
	                settings_fields( 'gmaps_locator_options' );
	                do_settings_sections( 'gmaps-locator' );
	                submit_button();
	            ?>
	            </form>
	        </div>
					<hr>
					<div class="wrap" style="max-width:800px;">
						<h2>Help! Something went wrong!</h2>
						<p>Sometimes things just aren't as easy as they should be and things break. It happens, it sucks, but here are some steps to find out what went wrong and why.</p>
						<h3>Your Google API Key is invalid</h3>
						<p>First thing's first, let's check out your API Key and make sure that it's accurate. If you're having problems with your Locator not working at all or you're getting console or alert errors then maybe the API Key wasn't copied in right. If it's not 100% correct then Google won't accept it. Enter it <em>exactly</em> as the API Console shows it, capitalizations and puncutation included.</p>
					</div>
	        <?php
	    }
			//admin options page settings
	    public function page_init(){
	        register_setting(
	            'gmaps_locator_options', // Option group
	            'locator_options', // Option name
	            array( $this, 'sanitize' ) // Sanitize
	        );

	        add_settings_section(
	            'gmaps-locator', // ID
	            'Locator Settings', // Title
	            array( $this, 'print_section_info' ), // Callback
	            'gmaps-locator' // Page
	        );

	        add_settings_field(
	            'google_api_key', // ID
	            'Google API Key', // Title
	            array( $this, 'google_api_key_callback' ), // Callback
	            'gmaps-locator', // Page
	            'gmaps-locator' // Section
	        );

					add_settings_field(
							'map_center', // ID
							'Map Center', // Title
							array( $this, 'map_center_callback' ), // Callback
							'gmaps-locator', // Page
							'gmaps-locator' // Section
					);
					add_settings_field(
							'gmaps_latitude', // ID
							'Latitude', // Title
							array( $this, 'gmaps_lat_callback' ), // Callback
							'gmaps-locator', // Page
							'gmaps-locator' // Section
					);
					add_settings_field(
							'gmaps_longitude', // ID
							'Longitude', // Title
							array( $this, 'gmaps_lng_callback' ), // Callback
							'gmaps-locator', // Page
							'gmaps-locator' // Section
					);
					add_settings_field(
							'zoom_level', // ID
							'Zoom Level', // Title
							array( $this, 'zoom_level_callback' ), // Callback
							'gmaps-locator', // Page
							'gmaps-locator' // Section
					);
	    }
	    //admin options page input sanitization
	    public function sanitize( $input ){
	        $new_input = array();
	        if( isset( $input['google_api_key'] ) )
	            $new_input['google_api_key'] = sanitize_text_field( $input['google_api_key'] );

					if( isset( $input['gmaps_longitude'] ) )
							$new_input['gmaps_longitude'] = sanitize_text_field( $input['gmaps_longitude'] );

					if( isset( $input['gmaps_latitude'] ) )
							$new_input['gmaps_latitude'] = sanitize_text_field( $input['gmaps_latitude'] );

					if( isset( $input['zoom_level'] ) )
							$new_input['zoom_level'] = sanitize_text_field( $input['zoom_level'] );
					return $new_input;
	    }
	    public function print_section_info(){
	        print 'Enter your settings below:';
	    }
	    public function google_api_key_callback(){
	        printf(
	            '<input type="text" id="google_api_key" name="locator_options[google_api_key]" value="%s" size="38"/>
							<br><em>If you do not have a Google API Key, <a href="https://developers.google.com/maps/documentation/javascript/tutorial#api_key">check here</a> to get one.</em>',
	            isset( $this->settings['google_api_key'] ) ? esc_attr( $this->settings['google_api_key']) : ''
	        );
	    }
			public function map_center_callback(){
					printf(
							'<em>Zoom and Pan the Map below to determine the default latitude, longitude, and zoom of the map. Or, if you\'re feeling adventurous, add in your own lat, long, and zoom levels in the fields below.</em>
							<div id="gmaps_locator_map"></div>'
					);
			}
			public function gmaps_lat_callback(){
					printf(
							'<input type="text" id="gmaps_latitude" name="locator_options[gmaps_latitude]" value="%s" size="38"/>',
							isset( $this->settings['gmaps_latitude'] ) ? esc_attr( $this->settings['gmaps_latitude']) : ''
					);
			}
			public function gmaps_lng_callback(){
					printf(
							'<input type="text" id="gmaps_longitude" name="locator_options[gmaps_longitude]" value="%s" size="38"/>',
							isset( $this->settings['gmaps_longitude'] ) ? esc_attr( $this->settings['gmaps_longitude']) : ''
					);
			}
			public function zoom_level_callback(){
					printf(
							'<input type="number" min="1" max="18" id="zoom_level" name="locator_options[zoom_level]" value="%s" />',
							isset( $this->settings['zoom_level'] ) ? esc_attr( $this->settings['zoom_level']) : '1'
					);
			}

			//shortcode & register for Locator
			public function locator_shortcode_enqueue(){
				//register
				function gmaps_locator_scripts() {
					wp_register_script( 'gmaps-locator', 'https://maps.googleapis.com/maps/api/js?libraries=places&key='.$settings['google_api_key']);
					wp_register_script( 'gmaps-locator-script',GMAPS_LOCATOR_URL.'/assets/js/GMAPS_LOCATOR.js');
					wp_register_style( 'gmaps-locator-style',GMAPS_LOCATOR_URL.'/assets/css/GMAPS_LOCATOR.css');
				} add_action( 'wp_enqueue_scripts', 'gmaps_locator_scripts' );

				//shortcode
				function locator_shortcode($atts){
					$a = shortcode_atts(array(
						'search' => true,
						'tags'   => true,
						'debug'  => false,
						'geolocate' => true
					),$atts);

					//create locations array for JSON
					$settings = get_option( 'locator_options' );
					$args = array( 'posts_per_page' => -1,
							'offset'           => 0,
							'category'         => '',
							'orderby'          => 'post_date',
							'order'            => 'DESC',
							'include'          => '',
							'exclude'          => '',
							'meta_key'         => '',
							'meta_value'       => '',
							'post_type'        => 'gmaps_locations',
							'post_mime_type'   => '',
							'post_parent'      => '',
							'post_status'      => 'publish',
							'suppress_filters' => true );
					$posts = get_posts( $args );
					$locs = array();
					$i = 0;
					foreach($posts as $post){
						$locs[$i]['ID'] = $post->ID;
						$locs[$i]['title'] = $post->post_title;
						$locs[$i]['coordinates'] = get_post_meta($post->ID,'coordinates',true);
						$locs[$i]['infowindow'] = get_post_meta($post->ID,'infowindow',true);
						$i++;
					}
					$settings['locations'] = $locs;
					$settings['ajax_url'] = admin_url( 'admin-ajax.php' );
					$settings['shortcode'] = $a;
					wp_localize_script( 'gmaps-locator-script', 'gmaps_locator_data', $settings );

					//enqueue scripts
					wp_enqueue_script('gmaps-locator');
					wp_enqueue_script('gmaps-locator-script');
					wp_enqueue_style('gmaps-locator-style');

					//shortcode logic
					if($a['debug'] == true){
						$debug .= '<hr><h6>Locator Debug:</h6>';
						$debug .= 'search = '.$a['search'].'<br>';
						$debug .= 'tags = '.$a['tags'].'<br>';
						$debug .= '<br><br><hr>';
					}
					$locator = '<input id="pac-input" class="controls" type="text" placeholder="Search Box">
											<div id="gmaps-locator"></div>
											<div id="gmaps-locator-radius" class="cf"></div>';
					if($a['search'] == true){
						$locator .= '<div id="gmaps-locator-search" class="cf">
														<input type="text" name="gmaps-locator-searchbox" id="gmaps-locator-searchbox" placeholder="find locations" />
														<div id="gmaps-locator-search-results"></div>
												 </div>';
					}
					if($a['filter'] == true){
						$locator .= '<div id="gmaps-locator-filter" class="cf">

												 </div>';
					}
					return $debug . $locator;
				} add_shortcode('gmaps_locator','locator_shortcode');
			}

			//location post type and taxonomy
			public function locator_posttype_tax(){
				add_action( 'init', 'locator_init' );
				function locator_init() {
					$labels = array(
						'name'               => _x( 'Locations', 'post type general name', 'GMAPS_LOCATOR' ),
						'singular_name'      => _x( 'Location', 'post type singular name', 'GMAPS_LOCATOR' ),
						'menu_name'          => _x( 'Locations', 'admin menu', 'GMAPS_LOCATOR' ),
						'name_admin_bar'     => _x( 'Location', 'add new on admin bar', 'GMAPS_LOCATOR' ),
						'add_new'            => _x( 'Add New', 'location', 'GMAPS_LOCATOR' ),
						'add_new_item'       => __( 'Add New Location', 'GMAPS_LOCATOR' ),
						'new_item'           => __( 'New Location', 'GMAPS_LOCATOR' ),
						'edit_item'          => __( 'Edit Location', 'GMAPS_LOCATOR' ),
						'view_item'          => __( 'View Location', 'GMAPS_LOCATOR' ),
						'all_items'          => __( 'All Locations', 'GMAPS_LOCATOR' ),
						'search_items'       => __( 'Search Locations', 'GMAPS_LOCATOR' ),
						'parent_item_colon'  => __( 'Parent Locations:', 'GMAPS_LOCATOR' ),
						'not_found'          => __( 'No locations found.', 'GMAPS_LOCATOR' ),
						'not_found_in_trash' => __( 'No locations found in Trash.', 'GMAPS_LOCATOR' )
					);
					$args = array(
						'labels'             => $labels,
						'public'             => true,
						'publicly_queryable' => false,
						'show_ui'            => true,
						'show_in_menu'       => true,
						'query_var'          => true,
						'capability_type'    => 'post',
						'has_archive'        => false,
						'hierarchical'       => false,
						'menu_position'      => null,
						'supports'           => array('title','custom-fields')
					); register_post_type( 'gmaps_locations', $args );
				}
			}

		}

	}
