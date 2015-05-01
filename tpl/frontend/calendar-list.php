<?php include(get_template_directory().'/../../plugins/wpclndr/tpl/frontend/header.php'); ?>
<?php
if (count($events)<1){
				?><h2>No events were found for this date range! Please use a different date range and try again.</h2><?php
} else {
				?>
				<section id="wpclndr-events-container">
								<div id="wpclndr-events-view" class="wpclndr-events-view-list">
								<?php
								//Loop through each event
								foreach($events as $event){
												?>
												<div class="wpclndr-events-listing">
																<h2 class="entry-title wpclndr-events-listing-header">
																				<span>
																								<a href="<?php echo get_permalink($event->ID); ?>" title="<?php echo $event->post_title; ?>"><?php echo $event->post_title; ?></a>
																				</span>
																</h2>
																<div class="wpclndr-events-listing-content">
																				<?php
																								if ($event->ALLDAY==1){
																												?><h3 class="wpclndr-allday-event wpclndr-date-header">
																																<?php
																																if (date('M jS, Y', strtotime($event->START))==date('M jS, Y', strtotime($event->END))){
																																				?>Date: <?php echo date('M jS, Y', strtotime($event->START)); ?> &ndash; All Day<?php
																																} else {
																																				?>Date: <?php echo date('M jS, Y', strtotime($event->START)); ?>
																																				to <?php echo date('M jS, Y', strtotime($event->END)); ?> &ndash; All Day<?php
																																}
																																?>
																												</h3><?php
																								} else {
																												?>
																												<h3 class="wpclndr-date-header">
																												Date: <?php echo date('M jS, Y', strtotime($event->START)); ?><?php echo date('H:i:s', strtotime($event->START))!='00:00:00' ? ' at '.date('g:i A', strtotime($event->START)) : ''; ?>
																												to <?php echo date('M jS, Y', strtotime($event->END)); ?><?php echo date('H:i:s', strtotime($event->END))!='00:00:00' ? ' at '.date('g:i A', strtotime($event->END)) : ''; ?>
																												</h3>
																												<?php
																								}
																				?>
																				<?php echo (strlen($event->post_content)>=WPCLNDR_STRING_LENGTH_ELLIPSIS ? $wpclndr->str_truncate($event->post_content, WPCLNDR_STRING_LENGTH_ELLIPSIS, array('html' => true, 'ending' => '')).'&hellip;' : $event->post_content); ?>
																</div>
												</div>
								<?php } ?>
								</div>
				</section>
<?php } ?>
<?php include(get_template_directory().'/../../plugins/wpclndr/tpl/frontend/footer.php'); ?>