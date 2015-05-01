<?php
/*******************************************************************************
 * Define our initial class
 ******************************************************************************/
class WPClndr{
				//Instantiate our public variables
				public $model, $plugin_path, $plugin_uri, $url, $post, $first_event = null,
								$last_event = null, $section = 'all', $json = '{}', $view = 'month',
								$sort = 'START', $order = 'asc', $start, $end;
				
				/*******************************************************************************
				 * Instantiate our constructor
				 ******************************************************************************/
				public function __construct(){
								//Call the init function
								$this->init();
				}
				
				/*******************************************************************************
				 * Perform initialization functions
				 ******************************************************************************/
				public function init(){
								//Init variables
								$this->plugin_path = __DIR__.'/..';
								$this->plugin_uri = str_replace('/lib', '', plugin_dir_url(__FILE__));
								$this->url = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off' || $_SERVER['SERVER_PORT']==443) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
								
								//Init our model
								$this->model = new WPClndr_Model();
								
								//Get the min and max dates
								$this->first_event = $this->model->get_min_date();
								$this->last_event = $this->model->get_max_date();
								
								//Init our hooks
								$this->init_hooks();
								
								//Initialize custom actions
								$this->init_custom_actions();
								
								//Init filters
								$this->init_filters();
								
								//Init shortcodes
								$this->init_shortcodes();
								
								//Initialize our JSON data
								$this->json = '{"first_event": {"date": "'.date(WPCLNDR_DATE_MYSQL, strtotime(str_replace('-', '/', $this->first_event))).'","year": "'.date('Y', strtotime(str_replace('-', '/', $this->first_event))).'","month": "'.date('m', strtotime(str_replace('-', '/', $this->first_event))).'","day": "'.date('d', strtotime(str_replace('-', '/', $this->first_event))).'"},"last_event": {"date": "'.date(WPCLNDR_DATE_MYSQL, strtotime(str_replace('-', '/', $this->last_event))).'","year": "'.date('Y', strtotime(str_replace('-', '/', $this->last_event))).'","month": "'.date('m', strtotime(str_replace('-', '/', $this->last_event))).'","day": "'.date('d', strtotime(str_replace('-', '/', $this->last_event))).'"}, "site_info": { "plugin_url": "'.$this->plugin_uri.'" }}';
				}
				
				/*******************************************************************************
				 * Initializes our hooks
				 ******************************************************************************/
				public function init_hooks(){
								//Register our activation hook
								register_activation_hook($this->plugin_path.'/init.php', array(&$this, 'register_activation_hook'));
								
								//Get our post object during the wp_head action
								add_action('wp_head', array(&$this, 'wp_head'));
								
								//Create our custom post type and taxonomies
								add_action('init', array(&$this, 'register_post_type'));
								
								//Enqueue our frontend styles
								add_action('wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts'));
								
								//Enqueue our admin styles
								add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
								
								//Add custom columns to calendar listing
								add_action('manage_posts_custom_column', array(&$this, 'manage_posts_custom_column'), 99, 2);
								
								//Add metaboxes to the post type admin
								add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
								
								//Save the meta data when the post is saved
								add_action('save_post', array(&$this, 'save_post'));
								
								//Hook into the footer of the theme to add the JSON, which will give JS access to DB data
								add_action('admin_footer', array(&$this, 'display_json'));
								add_action('wp_footer', array(&$this, 'display_json'));
    }
				
				/*******************************************************************************
				 * Initializes custom actions
				 ******************************************************************************/
				public function init_custom_actions(){								
								//Add the Ajax actions for the display events function
								add_action('wp_ajax_wpclndr_display_events', array(&$this, 'wpclndr_ajax_display_events'));
								add_action('wp_ajax_nopriv_wpclndr_display_events', array(&$this, 'wpclndr_ajax_display_events'));
				}
				
				/*******************************************************************************
				 * Initializes our filters
				 ******************************************************************************/
				public function init_filters(){
								//Add our custom post columns
								add_filter('manage_wpclndr_posts_columns', array(&$this, 'manage_wpclndr_posts_columns'), 99);
								
								//Set default post type
								add_filter('option_default_post_format', array(&$this, 'option_default_post_format'));
								
								//Intialize the query variables
								add_filter('query_vars', array(&$this, 'query_vars'));
								
								//Hide certain metaboxes
								add_filter('default_hidden_meta_boxes', array(&$this, 'default_hidden_meta_boxes'), 10, 2);
    }
				
				/*******************************************************************************
				 * Initializes our shortcodes
				 ******************************************************************************/
				public function init_shortcodes(){
								//Displays the calendar
								add_shortcode('wpclndr', array(&$this, 'shortcode_wpclndr'));
								
								//Displays only a listing of events
								add_shortcode('wpclndr_list', array(&$this, 'shortcode_wpclndr_list'));
    }
				
				/*******************************************************************************
				 * Performs tasks on activation of our plugin
				 ******************************************************************************/
				public function register_activation_hook(){
								//Create the DB if it isn't created already
								$this->model->init_db();
								
								//Flush the rewrite rules so the slug works properly
								flush_rewrite_rules();
    }
				
				/*******************************************************************************
				 * Puts the post in a global plugin variable and get attachments
				 ******************************************************************************/
				public function wp_head(){
								global $wp_query;
								
								//Set the post object
								$this->post = isset($wp_query->post) ? $wp_query->post : new stdClass();
    }
				
				/*******************************************************************************
				 * Registers our custom post type and taxonomies
				 ******************************************************************************/
				public function register_post_type(){
								//First, register our taxonomy
								register_taxonomy(WPCLNDR_CUSTOM_TAXONOMY, WPCLNDR_CUSTOM_POST_TYPE, array(
												'labels'                     => array(
																'name'                       => WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s', 'Taxonomy General Name', WPCLNDR_CUSTOM_POST_TYPE,
																'singular_name'              => WPCLNDR_CUSTOM_TAXONOMY_DEFAULT, 'Taxonomy Singular Name', WPCLNDR_CUSTOM_POST_TYPE,
																'menu_name'                  => 'Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s', WPCLNDR_CUSTOM_POST_TYPE,
																'all_items'                  => 'All Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s', WPCLNDR_CUSTOM_POST_TYPE,
																'parent_item'                => 'Parent Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'', WPCLNDR_CUSTOM_POST_TYPE,
																'parent_item_colon'          => 'Parent Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.':', WPCLNDR_CUSTOM_POST_TYPE,
																'new_item_name'              => 'New Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.' Name', WPCLNDR_CUSTOM_POST_TYPE,
																'add_new_item'               => 'Add New Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'', WPCLNDR_CUSTOM_POST_TYPE,
																'edit_item'                  => 'Edit Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'', WPCLNDR_CUSTOM_POST_TYPE,
																'update_item'                => 'Update Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'', WPCLNDR_CUSTOM_POST_TYPE,
																'separate_items_with_commas' => 'Separate Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s with commas', WPCLNDR_CUSTOM_POST_TYPE,
																'search_items'               => 'Search Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s', WPCLNDR_CUSTOM_POST_TYPE,
																'add_or_remove_items'        => 'Add or remove Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s', WPCLNDR_CUSTOM_POST_TYPE,
																'choose_from_most_used'      => 'Choose from the most used Calendar '.WPCLNDR_CUSTOM_TAXONOMY_DEFAULT.'s', WPCLNDR_CUSTOM_POST_TYPE
												),
												'hierarchical'               => true,
												'public'                     => true,
												'show_ui'                    => true,
												'show_admin_column'          => true,
												'show_in_nav_menus'          => true,
												'show_tagcloud'              => false,
												'has_archive'																=> false
								));
								
								//Register custom post type
								register_post_type(WPCLNDR_CUSTOM_POST_TYPE, array(
												'label'               => WPCLNDR_CUSTOM_POST_TYPE, WPCLNDR_CUSTOM_POST_TYPE,
												'description'         => 'Calendar Events', WPCLNDR_CUSTOM_POST_TYPE,
												'labels'              => array(
																'name'                => 'Events',
																'singular_name'       => 'Event',
																'menu_name'           => 'Calendar', WPCLNDR_CUSTOM_POST_TYPE,
																'parent_item_colon'   => 'Parent Event:', WPCLNDR_CUSTOM_POST_TYPE,
																'all_items'           => 'All Events', WPCLNDR_CUSTOM_POST_TYPE,
																'view_item'           => 'View Event', WPCLNDR_CUSTOM_POST_TYPE,
																'add_new_item'        => 'Add New Event', WPCLNDR_CUSTOM_POST_TYPE,
																'add_new'             => 'New Event', WPCLNDR_CUSTOM_POST_TYPE,
																'edit_item'           => 'Edit Event', WPCLNDR_CUSTOM_POST_TYPE,
																'update_item'         => 'Update Event', WPCLNDR_CUSTOM_POST_TYPE,
																'search_items'        => 'Search events', WPCLNDR_CUSTOM_POST_TYPE,
																'not_found'           => 'No events found', WPCLNDR_CUSTOM_POST_TYPE,
																'not_found_in_trash'  => 'No events found in Trash', WPCLNDR_CUSTOM_POST_TYPE
												),
												'supports'            => array('title', 'editor'),
												'taxonomies'          => array(WPCLNDR_CUSTOM_TAXONOMY, WPCLNDR_CUSTOM_TAXONOMY_DEFAULT),
												'hierarchical'        => false,
												'public'              => true,
												'show_ui'             => true,
												'show_in_menu'        => true,
												'show_in_nav_menus'   => true,
												'show_in_admin_bar'   => true,
												'menu_position'       => 5,
												'menu_icon'           => 'dashicons-calendar',
												'can_export'          => true,
												'has_archive'         => false,
												'exclude_from_search' => false,
												'publicly_queryable'  => true,
												'rewrite'             => array(
																'slug'                => 'calendar',
																'with_front'          => true,
																'pages'               => true,
																'feeds'               => true
												),
												'capability_type'     => 'page'
								));
								
								//Init the base categories
								$base_categories = array(
												array(
																'name' => 'Miscellaneous',
																'description' => 'Events that cannot be filed anywhere else go here.'
												)
								);
								
								//Loop through each category and add it to the database
								foreach($base_categories as $cat){
												//Check if this particular section exists
												$term = term_exists($cat['name'], WPCLNDR_CUSTOM_TAXONOMY_DEFAULT);
												
												//If not, then we add it
												if ($term==0 || $term==null) wp_insert_term($cat['name'], WPCLNDR_CUSTOM_TAXONOMY, array('description' => $cat['description']));
								}
								
								//Flush the rewrite rules so the slug works properly
								flush_rewrite_rules();
				}
				
				/*******************************************************************************
				* Add custom columns to the post listing for our custom post type
				******************************************************************************/
				public function manage_wpclndr_posts_columns($columns){
							//Remove the date column from the existing array
							unset($columns['date']);
							
							//Remove the sections column. We are adding this back so it moves to the end
							//unset($columns['taxonomy-wpclndr_cat']);
							
							//Remove the SEO columns
							unset($columns['wpseo-score']);
							unset($columns['wpseo-title']);
							unset($columns['wpseo-metadesc']);
							unset($columns['wpseo-focuskw']);
							
							//Add the new items to the array
							return array_merge($columns, array(
											'description' => 'Description',
											'taxonomy-wpclndr_cat' => 'Sections',
											'start_date' => 'Start Date',
											'end_date' => 'End Date',
											'featured' => 'Featured?'
							));
				}
				
				/*******************************************************************************
				* Adds content to the columns added in the function above
				******************************************************************************/
				public function manage_posts_custom_column($column, $post_id){
								global $wpdb;
								
							//Get the post object for this post ID
							$post = get_post($post_id);
							
							//Check which column is being rendered
							switch($column){
											case 'description':
															echo do_shortcode(strlen($post->post_content)>400 ? substr($post->post_content, 0, 400).'&hellip;' : $post->post_content);
															break;
											case 'start_date':
															$date = $wpdb->get_row('SELECT START, ALLDAY FROM '.$wpdb->prefix.'wpclndr_events WHERE WP_ID = '.$post->ID);
															echo empty($date) ? 'Not Set' : date('M j, Y'.(empty($date->ALLDAY) ? ' @ g:iA' : ''), strtotime($date->START)).($date->ALLDAY==1 ? '<br />All Day Event' : '');
															break;
												case 'end_date':
															$date = $wpdb->get_row('SELECT END, ALLDAY FROM '.$wpdb->prefix.'wpclndr_events WHERE WP_ID = '.$post->ID);
															echo empty($date) ? 'Not Set' : date('M j, Y'.(empty($date->ALLDAY) ? ' @ g:iA' : ''), strtotime($date->END)).($date->ALLDAY==1 ? '<br />All Day Event' : '');
															break;
												case 'featured':
															$featured = $wpdb->get_var('SELECT FEATURED FROM '.$wpdb->prefix.'wpclndr_events WHERE WP_ID = '.$post->ID);
															echo $featured==1 ? 'Yes' : 'No';
															break;
							}
				}
				
				/*******************************************************************************
				 * Sets the default post type
				 ******************************************************************************/
				public function option_default_post_format($format){
        global $post_type;
    
        return ($post_type==WPCLNDR_CUSTOM_POST_TYPE ? WPCLNDR_CUSTOM_POST_TYPE : $format);
    }
				
				/*******************************************************************************
				 * Initializes the rewrite rules
				 ******************************************************************************/
				public function rewrite_rules_array($rules) {
								array_push($rules, array('view/(.+)/([^/]+)$' => 'index.php?pagename=calendar&view=$matches[2]'));
								return $rules;
				}
				
				/*******************************************************************************
				 * Initializes the query variables
				 ******************************************************************************/
				public function query_vars($query_vars){
								array_push($query_vars, 'view', 'section', 'sort', 'start', 'end');
								return $query_vars;
				}
				
				/*******************************************************************************
				 * Hide unnecessary meta boxes
				 ******************************************************************************/
				public function default_hidden_meta_boxes($hidden, $screen){
								//If this is our custom post type, then we want to hide the following meta boxes on the new posts page
								if ($screen->post_type==WPCLNDR_CUSTOM_POST_TYPE) array_push($hidden, 'postexcerpt');
												
								return $hidden;
				}
				
				/*******************************************************************************
				 * Create the meta boxes
				 ******************************************************************************/
    public function add_meta_boxes() {
        add_meta_box('wpclndr-add-meta-box-create-event', WPCLNDR_PLUGIN_NAME.' &ndash; Create Event', array(&$this, 'add_meta_box_create_event'), WPCLNDR_CUSTOM_POST_TYPE, 'side', 'core');
    }
				
				/*******************************************************************************
				 * Metabox: Create Event
				 ******************************************************************************/
    public function add_meta_box_create_event($post){
        global $wpdb;
        
        //Get the event data
        $data = $this->model->get_event_by_post_id($post->ID);
								
								//Process data
								$data = isset($data->ALLDAY) && !empty($data->ALLDAY) ? $data : (object) array_merge((array)$data, array('ALLDAY' => 0));
								
								//Load the meta box template
        include($this->plugin_path.'/tpl/admin/metabox_create_event.php');
    }
				
				/*******************************************************************************
				 * Save the meta data
				 ******************************************************************************/
				public function save_post($post_id){
								//Set the post ID if it exists in the $_POST array
								$post_id = isset($_POST['post_ID']) ? $_POST['post_ID'] : $post_id;
								
								//Save events data to post
								if (isset($_POST['post_type']) && $_POST['post_type']==WPCLNDR_CUSTOM_POST_TYPE){
												global $wpdb;
												
												//Process POST variables
												$start_date = date(WPCLNDR_DATE_MYSQL_NOTIME, strtotime(str_replace('-', '/', $_POST['wpclndr_start_date']))).' '.(!empty($_POST['wpclndr_start_time']) ? date(WPCLNDR_TIME_MYSQL_NODATE, strtotime($_POST['wpclndr_start_time'])) : '00:00:00');
												$start_time = date(WPCLNDR_TIME_MYSQL_NODATE, strtotime($_POST['wpclndr_start_date']));
												$end_date = date(WPCLNDR_DATE_MYSQL_NOTIME, strtotime(str_replace('-', '/', $_POST['wpclndr_end_date']))).' '.(!empty($_POST['wpclndr_end_time']) ? date(WPCLNDR_TIME_MYSQL_NODATE, strtotime($_POST['wpclndr_end_time'])) : '00:00:00');
												$end_time = date(WPCLNDR_TIME_MYSQL_NODATE, strtotime($_POST['wpclndr_end_date']));
												$allday = (isset($_POST['wpclndr_allday']) ? 1 : 0);
												$featured = (isset($_POST['wpclndr_featured']) ? 1 : 0);
												$location = (isset($_POST['wpclndr_location']) ? $_POST['wpclndr_location'] : '');
												$recurring = (isset($_POST['wpclndr_recurring']) ? $_POST['wpclndr_recurring'] : '');
												$recurring_days = (isset($_POST['wpclndr_recurring_days']) ? $_POST['wpclndr_recurring_days'] : '');
												
												//Save the meta data
												update_post_meta($post_id, 'wpclndr_start_date', $start_date);
												update_post_meta($post_id, 'wpclndr_start_time', $start_time);
												update_post_meta($post_id, 'wpclndr_end_date', $end_date);
												update_post_meta($post_id, 'wpclndr_end_time', $end_time);
												update_post_meta($post_id, 'wpclndr_allday', $allday);
												update_post_meta($post_id, 'wpclndr_featured', $featured);
												update_post_meta($post_id, 'wpclndr_location', $location);
												update_post_meta($post_id, 'wpclndr_recurring', $recurring);
												update_post_meta($post_id, 'wpclndr_recurring_days', $recurring_days);
												
												//Check if the event exists in our events table
												$exists_query = $wpdb->get_row('SELECT ID FROM '.WPCLNDR_DB_TABLE_EVENTS.' WHERE WP_ID = '.$post_id);
												
												//Update or insert the row
												if (count($exists_query)>0){
																//Update the existing row
																$wpdb->update(WPCLNDR_DB_TABLE_EVENTS, array('WP_ID' => $post_id, 'START' => $start_date, 'END' => $end_date, 'ALLDAY' => $allday, 'FEATURED' => $featured, 'LOCATION' => $location, 'RECURRING' => $recurring, 'RECURRING_DAYS' => $recurring_days), array('WP_ID' => $post_id));
												} else {
																//Insert the event to the events table
																$wpdb->insert(WPCLNDR_DB_TABLE_EVENTS, array('WP_ID' => $post_id, 'START' => $start_date, 'END' => $end_date, 'ALLDAY' => $allday, 'FEATURED' => $featured, 'LOCATION' => $location, 'RECURRING' => $recurring, 'RECURRING_DAYS' => $recurring_days));
												}
												
												//If the recurring option was checked, set up a wp_cron job
												/*if (!empty($recurring)){
																//Set up the new wp_cron schedule
																add_filter('cron_schedules', function($schedules){
																				//Verify the schedule does not already exist
																				if (!isset($schedules['every_'.$recurring_days.'_days'])){
																								//Get the timestamp of the recurring event
																								$interval = strtotime('+'.$recurring_days.' days', time());
																								
																								//Add the new schedule
																								$schedules['every_'.$recurring_days.'_days'] = array(
																												'interval' => $interval,
																												'display'  => 'Every '.$recurring_days.' days'
																								);
																								
																								return $schedules;
																				}
																});
																
																//Set up the cron job
																wp_schedule_event($interval, 'every_'.$recurring_days.'_days', function(){
																				
																});
												}*/
								}
    }
				
				/*******************************************************************************
				 * Displays the JSON code on the template
				 ******************************************************************************/
				public function display_json($view = 'month'){
								echo '
												<script type="text/javascript">
																if (typeof(WPClndr)=="undefined") var WPClndr = {}; //Init the WPClndr object, if it is not already defined
																WPClndr.json = '.$this->json.';
																WPClndr.view = "'.$view.'";
												</script>
								';
				}
				
				/*******************************************************************************
				 * Shortcode for displaying the calendar on the frontend
				 ******************************************************************************/
				public function shortcode_wpclndr($atts){
								//Extract the shortcode attributes
        extract(shortcode_atts(array(
												'category' => '',
												'numevents' => '-1'
								), $atts));
								
								//Set the variables
								$start = empty($_GET['start']) ? $this->start : $_GET['start'];
								$start = empty($start) ? date(WPCLNDR_DATE_SLASHES_LEADING, strtotime('today')) : $start;
								$view = empty($_GET['view']) ? $this->view : $_GET['view'];
								$section = empty($_GET['section']) ? $this->section : $_GET['section'];
								$sort = empty($_GET['sort']) ? $this->sort : $_GET['sort'];
								
								//Get the events from the DB
								$events = $this->model->get_events($start, '', $numevents, 'asc', 'START', $section, $section=='all' ? true : false);
        
        //Initialize the JSON for our shortcode
        $json = json_encode(array(
            'hasError' => false,
            'error' => '',
            'eventCount' => 0,
            'events' => $events,
            'query_vars' => ''
        ));
								
								//Determine which template to load and load it
								$theme_file = get_template_directory().'/wpclndr/tpl/calendar-'.$this->view.'.php';
								$plugin_file = $this->plugin_path.'/tpl/frontend/calendar-'.$this->view.'.php';
        include(file_exists($theme_file) ? $theme_file : $plugin_file);
    }
				
				/*******************************************************************************
				 * Shortcode for displaying only a listing of events on the frontend
				 ******************************************************************************/
				public function shortcode_wpclndr_list($atts){
								//Extract the shortcode attributes
        extract(shortcode_atts(array(
												'section' => 'all',
												'numevents' => '-1',
												'title' => 'Events'
								), $atts));
								
								//Get the events from the DB
								$events = $this->model->get_events_by_section($numevents, $section);
								
								//Determine which template to load and load it
								$theme_file = get_template_directory().'/wpclndr/tpl/calendar-listing-only.php';
								$plugin_file = $this->plugin_path.'/tpl/frontend/calendar-listing-only.php';
								
								//Start the outpout buffer so we can return our template
								ob_start();
												
												//Include our template file
												include(file_exists($theme_file) ? $theme_file : $plugin_file);
												
												//Get the HTML from the template
												$html = ob_get_contents();
												
								//Close and clean the output buffer
								ob_end_clean();
								
        return $html;
    }
				
				/*******************************************************************************
				 * Loops through events and displays them accordingly
				 ******************************************************************************/
				public function display_events($view = 'month', $events = array(), $echo = true) {
								global $wp;
								
								//Get the section
								$section = (isset($POST['section']) ? $POST['section'] : (!empty($section) ? $section : 'all'));
								
								//Get the times
								$start_date = (isset($POST['date_start']) ? $POST['date_start'] : $start_date);
								$end_date = (isset($POST['date_end']) ? $POST['date_end'] : $end_date);
								
								//Check if events have been passed. If not, we use what's in $POST
								$events = (empty($events) ? $this->model->get_events() : $events);
								
								//Get the events and, assuming any exist, generate HTML for them
								$html = (count($events)>0 ? '<section id="wpclndr-events-container"><div id="wpclndr-events-view" class="wpclndr-events-view-'.$view.'">' : '<h2>No events were found for this date range! Please use a different date range and try again.</h2>');
								
								//Loop through each event
								foreach($events as $event){
												$html .= '
																<div class="wpclndr-events-listing">
																				<h3 class="wpclndr-events-listing-header">
																				'.($event->ALLDAY==1 ? '<span class="wpclndr-allday-event">All Day Event</span>' : '<span>'.date(WPCLNDR_DATE_TIME_SHORT, strtotime($event->START)).'<br />'.date(WPCLNDR_DATE_TIME_SHORT, strtotime($event->END)).'</span>').'
																				<a href="'.get_permalink($event->ID).'" title="'.$event->post_title.'">'.$event->post_title.'</a></h3>
																				<div class="wpclndr-events-listing-content">
																								'.(strlen($event->post_content)>=WPCLNDR_STRING_LENGTH_ELLIPSIS ? $utils->strtrunc($event->post_content, WPCLNDR_STRING_LENGTH_ELLIPSIS, true, true) : $event->post_content).'
																				</div>
																</div>
												';
								}
								
								//Close the HTML
								$html .= (count($events = wpclndr_get_events())>0 ? '</div></div>' : '');
								
								//Echo or return the value, based on the parameters passed
								if ($echo) echo $html; else return $html;
				}

				/*******************************************************************************
				 * Ajax wrapper for display events function
				 ******************************************************************************/
    public function wpclndr_ajax_display_events(){
								global $wpdb;
								
								//Set the view
								$view = isset($_POST['view']) ? $_POST['view'] : 'month';
								
								//Process the sort by field
								$sort_by = isset($_POST['sort']) && $view=='list' ? $_POST['sort'] : 'asc';
								$sort = $sort_by=='START' ? 'asc' : $sort_by;
								
								//Get the section
								$section = isset($_POST['section']) ? $_POST['section'] : 'all';
								
								//Get the times
								$start = $view=='list' ? isset($_POST['date_start']) ? $_POST['date_start'] : date('m/d/Y', strtotime('now')) : null;
								$end = $view=='list' ? isset($_POST['date_end']) ? $_POST['date_end'] : '' : null;
								
								//Get the events
								$events = $this->model->get_events($start, $end, -1, $sort, $sort_by, $section, false, $view);
								
								//Get the template filename, or use the default if not set
								$template_filename = empty($view) ? WPCLNDR_DEFAULT_TEMPLATE.'.php' : 'calendar-'.$view.'.php';
								$theme_file = get_template_directory().'/wpclndr/tpl/'.$template_filename;
								$plugin_file = $this->plugin_path.'/tpl/frontend/'.$template_filename;
								
								//Echo out template to the ajax call
								include(file_exists($theme_file) ? $theme_file : $plugin_file);
								
								//Make sure the script exits so no other data is echoed out
								die();
				}
				
				/*******************************************************************************
				 * Registers scripts and styles to be placed in the admin header
				 ******************************************************************************/
				public function admin_enqueue_scripts(){
								//Set the script dependencies
								$deps = array('jquery');
								
								//Enqueue our styles
								wp_enqueue_style('wpclndr-dashicons-style', '//cdn.jsdelivr.net/dashicons/0.1.0/css/dashicons.min.css');
								wp_enqueue_style('jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('wpclndr-admin-table-style', '//cdn.jsdelivr.net/jquery.datatables/1.9.4/css/jquery.dataTables.css');
								wp_enqueue_style('wpclndr-fullcalendar-style', '//cdn.jsdelivr.net/fullcalendar/2.0.1/fullcalendar.css');
								wp_enqueue_style('wpclndr-timepicker-style', '//cdn.jsdelivr.net/jquery.timepicker/1.2.1/jquery.timepicker.css');
        wp_enqueue_style('wpclndr-admin-style', $this->plugin_uri.'assets/css/admin.css');
								
								//Enqueue our scripts
								wp_enqueue_script('jquery-ui-datepicker');
								wp_enqueue_script('wpclndr-admin-table-script', '//cdn.jsdelivr.net/jquery.datatables/1.9.4/js/jquery.dataTables.min.js', $deps);
								wp_enqueue_script('wpclndr-momentjs-script', '//cdn.jsdelivr.net/momentjs/2.8.3/moment-with-locales.min.js', $deps);
								wp_enqueue_script('wpclndr-fullcalendar-script', '//cdn.jsdelivr.net/fullcalendar/2.0.1/fullcalendar.min.js', $deps);
								wp_enqueue_script('wpclndr-monthpicker-script', $this->plugin_uri.'assets/js/lib/jquery.mtz.monthpicker.js', $deps);
								wp_enqueue_script('wpclndr-timepicker-script', '//cdn.jsdelivr.net/jquery.timepicker/1.2.1/jquery.timepicker.min.js');
        wp_enqueue_script('wpclndr-admin-script', $this->plugin_uri.'assets/js/admin.js', $deps);
				}
				
				/*******************************************************************************
				 * Registers scripts and styles to be placed in the frontend header
				 ******************************************************************************/
				public function wp_enqueue_scripts(){
								//Set the script dependencies
								$deps = array('jquery');
								
								//Enqueue all the frontend styles
								wp_enqueue_style('wpclndr-google-web-fonts', 'http://fonts.googleapis.com/css?family=Open+Sans:400,300,700,600,800|Open+Sans+Condensed:300,700');
								wp_enqueue_style('jquery-ui-datepicker', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('wpclndr-admin-table-style', '//cdn.jsdelivr.net/jquery.datatables/1.9.4/css/jquery.dataTables.css');
								wp_enqueue_style('wpclndr-fullcalendar-style', '//cdn.jsdelivr.net/fullcalendar/2.0.1/fullcalendar.css');
        wp_enqueue_style('wpclndr-style', $this->plugin_uri.'assets/css/frontend.css');
								wp_enqueue_style('wpclndr-responsive-style', $this->plugin_uri.'assets/css/frontend.responsive.css');
								
								//Enqueue all the frontend scripts
								wp_enqueue_script('jquery-ui-datepicker');
								wp_enqueue_script('wpclndr-admin-table-script', '//cdn.jsdelivr.net/jquery.datatables/1.9.4/js/jquery.dataTables.min.js', $deps);
								wp_enqueue_script('wpclndr-momentjs-script', '//cdn.jsdelivr.net/momentjs/2.8.3/moment-with-locales.min.js', $deps);
								wp_enqueue_script('wpclndr-fullcalendar-script', '//cdn.jsdelivr.net/fullcalendar/2.0.1/fullcalendar.min.js', $deps);
								wp_enqueue_script('wpclndr-monthpicker-script', $this->plugin_uri.'assets/js/lib/jquery.mtz.monthpicker.js', $deps);
								wp_enqueue_script('wpclndr-script', $this->plugin_uri.'assets/js/frontend.js', $deps);
				}
				
				/**********************************************************************************************
				 * Truncates a string without breaking HTML or splitting words
				 *********************************************************************************************/
				public function str_truncate($text, $length = 100, $options = array()) {
								$default = array(
												'ending' => '', 'exact' => true, 'html' => false
								);
								$options = array_merge($default, $options);
								extract($options);
				
								if ($html) {
												if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
																return $text;
												}
												$totalLength = mb_strlen(strip_tags($ending));
												$openTags = array();
												$truncate = '';
				
												preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
												foreach ($tags as $tag) {
																if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
																				if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
																								array_unshift($openTags, $tag[2]);
																				} else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
																								$pos = array_search($closeTag[1], $openTags);
																								if ($pos !== false) {
																												array_splice($openTags, $pos, 1);
																								}
																				}
																}
																$truncate .= $tag[1];
				
																$contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
																if ($contentLength + $totalLength > $length) {
																				$left = $length - $totalLength;
																				$entitiesLength = 0;
																				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
																								foreach ($entities[0] as $entity) {
																												if ($entity[1] + 1 - $entitiesLength <= $left) {
																																$left--;
																																$entitiesLength += mb_strlen($entity[0]);
																												} else {
																																break;
																												}
																								}
																				}
				
																				$truncate .= mb_substr($tag[3], 0 , $left + $entitiesLength);
																				break;
																} else {
																				$truncate .= $tag[3];
																				$totalLength += $contentLength;
																}
																if ($totalLength >= $length) {
																				break;
																}
												}
								} else {
												if (mb_strlen($text) <= $length) {
																return $text;
												} else {
																$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
												}
								}
								if (!$exact) {
												$spacepos = mb_strrpos($truncate, ' ');
												if (isset($spacepos)) {
																if ($html) {
																				$bits = mb_substr($truncate, $spacepos);
																				preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
																				if (!empty($droppedTags)) {
																								foreach ($droppedTags as $closingTag) {
																												if (!in_array($closingTag[1], $openTags)) {
																																array_unshift($openTags, $closingTag[1]);
																												}
																								}
																				}
																}
																$truncate = mb_substr($truncate, 0, $spacepos);
												}
								}
								$truncate .= $ending;
				
								if ($html) {
												foreach ($openTags as $tag) {
																$truncate .= '</'.$tag.'>';
												}
								}
				
								return $truncate;
				}
}
?>