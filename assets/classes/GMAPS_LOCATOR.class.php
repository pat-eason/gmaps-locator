<?php
	//class for our plug-in logic
	if(!class_exists('GMAPS_LOCATOR') && class_exists('GMAPS_LOCATOR_Options')) {

		class GMAPS_LOCATOR extends GMAPS_LOCATOR_Options {
			private $table_map;
			public function __construct() {
				parent::__construct();
				$this->get_settings();
				$this->map_tables();

				//locator admin/settings page
				add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
				//locator shortcode and enqueue
				$this->locator_shortcode_enqueue();

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


			//admin options page
	    public function add_plugin_page(){
	        add_options_page(
	            'Settings Admin',
	            'My Settings',
	            'manage_options',
	            'my-setting-admin',
	            array( $this, 'create_admin_page' )
	        );
	    }

	    /**
	     * Options page callback
	     */
	    public function create_admin_page()
	    {
	        // Set class property
	        $this->options = get_option( 'my_option_name' );
	        ?>
	        <div class="wrap">
	            <?php screen_icon(); ?>
	            <h2>My Settings</h2>
	            <form method="post" action="options.php">
	            <?php
	                // This prints out all hidden setting fields
	                settings_fields( 'my_option_group' );
	                do_settings_sections( 'my-setting-admin' );
	                submit_button();
	            ?>
	            </form>
	        </div>
	        <?php
	    }

	    /**
	     * Register and add settings
	     */
	    public function page_init()
	    {
	        register_setting(
	            'my_option_group', // Option group
	            'my_option_name', // Option name
	            array( $this, 'sanitize' ) // Sanitize
	        );

	        add_settings_section(
	            'setting_section_id', // ID
	            'My Custom Settings', // Title
	            array( $this, 'print_section_info' ), // Callback
	            'my-setting-admin' // Page
	        );

	        add_settings_field(
	            'id_number', // ID
	            'ID Number', // Title
	            array( $this, 'id_number_callback' ), // Callback
	            'my-setting-admin', // Page
	            'setting_section_id' // Section
	        );

	        add_settings_field(
	            'title',
	            'Title',
	            array( $this, 'title_callback' ),
	            'my-setting-admin',
	            'setting_section_id'
	        );
	    }

	    /**
	     * Sanitize each setting field as needed
	     *
	     * @param array $input Contains all settings fields as array keys
	     */
	    public function sanitize( $input )
	    {
	        $new_input = array();
	        if( isset( $input['id_number'] ) )
	            $new_input['id_number'] = absint( $input['id_number'] );

	        if( isset( $input['title'] ) )
	            $new_input['title'] = sanitize_text_field( $input['title'] );

	        return $new_input;
	    }

	    /**
	     * Print the Section text
	     */
	    public function print_section_info()
	    {
	        print 'Enter your settings below:';
	    }

	    /**
	     * Get the settings option array and print one of its values
	     */
	    public function id_number_callback()
	    {
	        printf(
	            '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
	            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
	        );
	    }

	    /**
	     * Get the settings option array and print one of its values
	     */
	    public function title_callback()
	    {
	        printf(
	            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
	            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
	        );
	    }



			//shortcode & Enqueue for Locator
			public function locator_shortcode_enqueue(){
				//enqueue

				//shortcode
				function locator_shortcode($atts){
					$a = shortcode_atts(array(
						'search' => true,
						'tags'   => true
					),$atts);
					return "search = {$a['search']}";
				} add_shortcode('gmaps_locator','locator_shortcode');

			}

		}

	}
