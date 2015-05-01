<div class="sub-event-listing">
				<h3>Meeting Schedule</h3>
				<p>All meetings are open to the public.</p>
				<div class="sub-event-listing-content">
								<?php
								if (count($events)<1){
												?><h2>No events were found.</h2><?php
								} else {
												?>
												<div id="wpclndr-list-<?php echo uniqid(); ?>" class="wpclndr-list-only">
																<ul>
																<?php
																				foreach($events as $event){
																								$month_name = date(SWT_DATE_MONTH_NAME, strtotime($event->start_date));
																								$month_day = date(SWT_DATE_DAY, strtotime($event->start_date));
																								?>
																								<li>
																												<a href="<?php echo get_permalink($event->ID); ?>" title="<?php echo $event->post_title; ?>">
																																<div class="event-number<?php echo $month_name=='Jul' ? ' short-event-number' : ''; ?>">
																																				<h4>
																																								<?php echo $month_name; ?><br />
																																								<?php echo $month_day; ?>
																																				</h4>
																																</div>
																																<h3>
																																				<span class="wpclndr-event-title"><?php echo $event->post_title; ?></span>
																																				<span class="wpclndr-event-time"><?php echo date(SWT_TIME, strtotime($event->start_date)); ?> &ndash; <?php echo date(SWT_TIME, strtotime($event->end_date)); ?></span>
																																</h3>
																												</a>
																								</li>
																				<?php
																}
																?>
																</ul>
												</div>
								<?php } ?>
				</div>
</div>