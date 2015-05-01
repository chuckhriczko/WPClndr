<?php
require_once('constants.php');
/*******************************************************************************
 * Define our model class for data operations
 ******************************************************************************/
class WPClndr_Model{
				/*******************************************************************************
				 * Instantiate our constructor
				 ******************************************************************************/
				public function __construct(){
								
				}
				
				/*******************************************************************************
				 * Creates our DB table if it does not already exist
				 ******************************************************************************/
				public function init_db(){
								global $wpdb;
								
								//Create the database table
								return $wpdb->query('
												CREATE TABLE IF NOT EXISTS `wp_wpclndr_events` (
																`ID` int(11) NOT NULL AUTO_INCREMENT COMMENT \'Unique ID for the event\',
																`WP_ID` int(11) NOT NULL COMMENT \'Wordpress post ID ($post->ID)\',
																`EG_ID` int(11) NOT NULL COMMENT \'Event Group ID\',
																`START` datetime NOT NULL,
																`END` datetime NOT NULL,
																`ALLDAY` tinyint(1) NOT NULL,
																`ROW_CREATED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
																`FEATURED` int(11) NOT NULL DEFAULT \'0\',
																`LOCATION` text NOT NULL,
																`RECURRING` int(11) NOT NULL DEFAULT \'0\',
																`RECURRING_DAYS` int(11) NOT NULL DEFAULT \'0\',
																PRIMARY KEY (`ID`)
										) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT=\'Holds all events. Grouped events share the same EG_ID\' AUTO_INCREMENT=1;
								');
				}
				
				/*******************************************************************************
				 * Gets the date range of the first calendar event
				 ******************************************************************************/
				public function get_min_date() {
        global $wpdb;
    
        //Get the first and last posts in the events table
								$date_start = $wpdb->get_var('SELECT START FROM '.WPCLNDR_DB_TABLE_EVENTS.' ORDER BY START ASC LIMIT 1');
								
								//Set the global event variables
								return (!empty($date_start) ? $date_start : date(WPCLNDR_DATE_MYSQL, strtotime('now')));
    }
				
				/*******************************************************************************
				 * Gets the date range of the first calendar event
				 ******************************************************************************/
				public function get_max_date() {
        global $wpdb;
    
        //Get the first and last posts in the events table
								$date_end = $wpdb->get_var('SELECT END FROM '.WPCLNDR_DB_TABLE_EVENTS.' ORDER BY END DESC LIMIT 1');
								
								//Set the global event variables
								return (!empty($date_end) ? $date_end : date(WPCLNDR_DATE_MYSQL, strtotime('+31 days')));
    }
				
				/*******************************************************************************
				 * Primary event retrieval method
				 ******************************************************************************/
				public function get_events($date_start = '', $date_end = '', $posts_num = -1, $sort_order = 'asc', $sort_by = 'START', $section = 'all', $all = false, $view = 'month'){
								global $wpdb, $wpclndr;
								
								//If the section is a name, and not an ID. then we must get the section ID
								$section = (!empty($section) && $section!='all' ? !is_numeric($section) ? get_term_by((ctype_lower($section) ? 'slug' : 'name'), $section, WPCLNDR_CUSTOM_TAXONOMY) : $section : $section);
								
								//If we recieved an object from the above line, get the ID only
								$section = is_object($section) ? (isset($section->term_id) ? $section->term_id : 0) : $section;

								//Process the date ranges
								$date_start = empty($date_start) ? strtotime('today midnight') : $date_start;
								$date_end = empty($date_end) ? $wpclndr->last_event : $date_end;

								//Convert date ranges to timestamps if they are not already
								$date_start = (!is_int($date_start) && !empty($date_start) ? strtotime(str_replace('-', '/', $date_start)) : $date_start);
								$date_end = (!is_int($date_end) && !empty($date_end) ? strtotime(str_replace('-', '/', $date_end)) : $date_end);
								
								//Generate section query
								$section_from = strtolower($section)=='all' ? '' : ','.$wpdb->term_relationships.' AS tr, '.$wpdb->term_taxonomy.' AS tt';
								$section_where = strtolower($section)=='all' ? '' : ' AND tt.term_id = '.$section.' AND tr.term_taxonomy_id = tt.term_taxonomy_id AND tr.object_id = posts.ID AND wpclndr_events.WP_ID = tr.object_id ';
								
								//Get the events
								$wpclndr_events = $wpdb->get_results('
												SELECT
																posts.*,
																wpclndr_events.START,
																wpclndr_events.END,
																wpclndr_events.ALLDAY
												FROM
																'.$wpdb->prefix.'wpclndr_events AS wpclndr_events,
																'.$wpdb->posts.' AS posts'.$section_from.'
												WHERE
																'.($all ? '' : 'UNIX_TIMESTAMP(wpclndr_events.END) >= '.($date_start - 18000).' AND ').'
																'.($all ? '' : 'UNIX_TIMESTAMP(wpclndr_events.END) <= '.($date_end + 18000).' AND ').'
																posts.post_type = "'.WPCLNDR_CUSTOM_POST_TYPE.'" AND
																posts.post_status = "publish" AND
																posts.ID = wpclndr_events.WP_ID'.$section_where.'
												ORDER BY
																wpclndr_events.START '.$sort_order.',
																posts.post_title '.$sort_order.
												($posts_num > 0 ? 'LIMIT 0, '.$posts_num : '')
								);
								
								return $wpclndr_events;
				}
				
				/*******************************************************************************
				 * Returns a single event found by the post ID
				 ******************************************************************************/
				public function get_event_by_post_id($post_id){
								global $wpdb;
								
								return $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'wpclndr_events WHERE WP_ID = '.$post_id);
				}
				
				/********************************************************************************************
					* Gets the most recent events
					********************************************************************************************/
				public function get_events_by_section($numposts = -1, $section = 'all', $order_asc = true){
								global $wpdb;
								
								//Get the events
								$query = '
												SELECT
																DISTINCT(posts.ID),
																posts.post_title,
																posts.post_content,
																pm.meta_value AS end_date,
																(SELECT meta.meta_value FROM '.$wpdb->postmeta.' meta WHERE meta.post_id = posts.ID AND meta.meta_key IN ("news-manager-featured-start-date", "wpclndr_start_date")) AS start_date
												FROM
																'.$wpdb->postmeta.' pm,
																'.$wpdb->posts.' posts,
																'.$wpdb->terms.' terms,
																'.$wpdb->term_relationships.' tr,
																'.$wpdb->term_taxonomy.' tt
												WHERE
																pm.post_id = posts.ID AND
																pm.meta_key IN ("news-manager-featured-end-date", "wpclndr_end_date") AND
																UNIX_TIMESTAMP(pm.meta_value) > "'.strtotime('now').'" AND
																posts.post_status = "publish" AND
																'.(empty($section) || $section=='all' ? '' : (is_numeric($section) ? 'terms.term_id = '.$section.' AND ' : 'terms.name = "'.$section.'" AND ')).'
																tt.term_id = terms.term_id AND
																tr.term_taxonomy_id = tt.term_taxonomy_id AND
																tr.object_id = posts.ID
												ORDER BY
																start_date ASC
												'.(empty($numposts) || $numposts==-1 ? '' : 'LIMIT '.$numposts);
								
								return $order_asc ? $wpdb->get_results($query) : array_reverse($wpdb->get_results($query));
				}
}
?>