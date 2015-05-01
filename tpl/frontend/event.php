<?php
global $wpdb, $post, $wp_query, $wpclndr_view, $wpclndr_section, $wpclndr_sort, $wpclndr_start, $wpclndr_end;

//Process query strings
if (isset($wp_query->query_vars['view'])) $wpclndr_view = strtolower($wp_query->query_vars['view']);
if (isset($wp_query->query_vars['section'])) $wpclndr_section = ucwords($wp_query->query_vars['section']);
if (isset($wp_query->query_vars['sort'])) $wpclndr_sort = strtoupper($wp_query->query_vars['sort']);
if (isset($wp_query->query_vars['start'])) $wpclndr_start = $wp_query->query_vars['start'];
if (isset($wp_query->query_vars['end'])) $wpclndr_end = $wp_query->query_vars['end'];

//Get the event information for this event
$post = (object)array_merge((array)$wp_query->post, (array)$wpdb->get_row('SELECT ID AS event_id, START, END, ALLDAY FROM '.WPCLNDR_DB_TABLE_EVENTS.' WHERE WP_ID = '.$wp_query->post->ID.' LIMIT 1'));

//Check if we should redirect to the calendar view (one of the above is set)
if (isset($wp_query->query_vars['view']) || isset($wp_query->query_vars['section']) || isset($wp_query->query_vars['sort']) || isset($wp_query->query_vars['start']) || isset($wp_query->query_vars['end'])){
				require(dirname(__FILE__).'/calendar-'.strtolower($wpclndr_view).'.php');
} else {
				//Get the header for the template
				get_header();
				?>
				<div class="wpclndr-events-view-list">
								<div class="wpclndr-events-listing">
												<h3 class="wpclndr-events-listing-header">
																<?php
																				if ($event->ALLDAY==1){
																								?><span class="wpclndr-allday-event">All Day Event</span><?php
																				} else {
																								echo '<span>'.date(WPCLNDR_DATE_TIME_SHORT, strtotime($event->START)); ?><br /><?php echo date(WPCLNDR_DATE_TIME_SHORT, strtotime($event->END)).'</span>';
																				}
																?>
																<?php echo $post->post_title; ?>
												</h3>
												<div class="wpclndr-events-listing-content"><?php echo $post->post_content; ?></div>
								</div>
				</div>
				</section>
				<?php get_footer(); ?>
<?php } ?>