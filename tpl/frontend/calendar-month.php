<?php include('header.php'); ?>
<?php
if (count($events)<1){
				?><h2>No events were found for this date range! Please use a different date range and try again.</h2><?php
} else {
				?>
				<section id="wpclndr-events-container">
								<div id="wpclndr-events-view" class="wpclndr-events-view-month"></div>
								<script type="text/javascript">
												WPClndr.events = [
																<?php
																//Loop through each event
																foreach($events as $key=>$event){
																				$separator = ($key==count($events)-1 ? '' : ',');
																				?>
																				{"allDay": <?php echo ($event->ALLDAY==1 ? 'true' : 'false'); ?>, "start": "<?php echo date(WPCLNDR_DATE_MYSQL, strtotime($event->START)); ?>","end": "<?php echo date(WPCLNDR_DATE_MYSQL, strtotime($event->END)); ?>","start_short": "<?php echo date(WPCLNDR_DATE_TIME_SHORT, strtotime($event->START)); ?>","end_short": "<?php echo date(WPCLNDR_DATE_TIME_SHORT, strtotime($event->END)); ?>","title": "<?php echo $event->post_title; ?>","content": "<?php echo mysql_real_escape_string((strlen($event->post_content)>=WPCLNDR_STRING_LENGTH_ELLIPSIS ? substr($event->post_content, 0, WPCLNDR_STRING_LENGTH_ELLIPSIS) : $event->post_content)); ?>","permalink": "<?php echo get_permalink($event->ID); ?>"}<?php echo $separator; ?>
																<?php } ?>
												];
								</script>
				</section>
<?php } ?>
<?php $view = 'month'; ?>
<?php include('footer.php'); ?>