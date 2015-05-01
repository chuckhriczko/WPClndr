<div>
				<label for="wpclndr_featured">Featured?</label>
				<input type="checkbox" value="1" name="wpclndr_featured" id="wpclndr_featured"<?php echo isset($data->FEATURED) && $data->FEATURED==1 ? ' checked' : ''; ?> />
</div>
<div>
				<label for="wpclndr_allday">All Day Event?</label>
				<input type="checkbox" value="1" name="wpclndr_allday" id="wpclndr_allday"<?php echo isset($data->ALLDAY) && $data->ALLDAY==1 ? ' checked' : ''; ?> />
</div>
<?php /*<div>
				<label for="wpclndr_allday">Recurring Event?</label>
				<input type="checkbox" value="1" name="wpclndr_recurring" id="wpclndr_recurring"<?php echo isset($data->RECURRING) && $data->RECURRING==1 ? ' checked' : ''; ?> />
				<div id="wpclndr_recurring_days_container">
								<label for="wpclndr_recurring_days">How many days should pass before this event reoccurs?</label>
								<input type="text" name="wpclndr_recurring_days" id="wpclndr_recurring_days" value="<?php echo isset($data->RECURRING_DAYS) && !empty($data->RECURRING_DAYS) ? $data->RECURRING_DAYS : 30; ?>" />
				</div>
</div>*/ ?>
<div>
				<span>Start Date</span>
				<input type="text" class="datepicker" name="wpclndr_start_date" id="wpclndr_start_date" value="<?php echo isset($data->START) && !empty($data->START) ? date(WPCLNDR_DATE_SLASHES_LEADING, strtotime($data->START)) : date(WPCLNDR_DATE_SLASHES_LEADING, strtotime('now')); ?>" />
</div>
<div<?php echo $data->ALLDAY==1 ? ' class="hidden"' : ''; ?>>
				<span>Start Time</span>
				<input type="text" class="timepicker" name="wpclndr_start_time" id="wpclndr_start_time" autocomplete="off" value="<?php echo isset($data->START) ? date(WPCLNDR_TIME_TWELVE, strtotime($data->START)) : date(WPCLNDR_TIME_TWELVE, strtotime('now')); ?>" />
</div>
<div>
				<span>End Date</span>
				<input type="text" class="datepicker" name="wpclndr_end_date" id="wpclndr_end_date" value="<?php echo isset($data->END) && !empty($data->END) ? date(WPCLNDR_DATE_SLASHES_LEADING, strtotime($data->END)) : date(WPCLNDR_DATE_SLASHES_LEADING, strtotime('now')); ?>" />
</div>
<div<?php echo $data->ALLDAY==1 ? ' class="hidden"' : ''; ?>>
				<span>End Time</span>
				<input type="text" class="timepicker" name="wpclndr_end_time" id="wpclndr_end_time" autocomplete="off" value="<?php echo isset($data->END) ? date(WPCLNDR_TIME_TWELVE, strtotime($data->END)) : date(WPCLNDR_TIME_TWELVE, strtotime('now')); ?>" />
</div>
<div>
				<span>Location</span>
				<input type="text" name="wpclndr_location" id="wpclndr_location" <?php echo isset($data->LOCATION) && !empty($data->LOCATION) ? ' value="'.$data->LOCATION.'"' : ' value="'.WPCLNDR_DEFAULT_LOCATION.'"'; ?> />
</div>