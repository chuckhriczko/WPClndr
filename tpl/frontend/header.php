<?php global $wpclndr; ?>
<section id="wpclndr-container">
	<section id="wpclndr-header">
		<label for="wpclndr-view-select">View: </label>
		<select id="wpclndr-view-select">
			<option value="list"<?php echo ($view=='list' ? ' selected' : ''); ?>>List</option>
			<option value="month"<?php echo ($view=='month' || empty($view) ? ' selected="selected"' : ''); ?>>Month</option>
			<option value="week"<?php echo ($view=='week' ? ' selected="selected"' : ''); ?>>Week</option>
			<option value="day"<?php echo ($view=='day' ? ' selected="selected"' : ''); ?>>Day</option>
		</select>
		<label for="wpclndr-section-select">Section: </label>
		<select id="wpclndr-section-select">
			<option value="all"<?php echo ($section=='all' || empty($section) ? ' selected' : ''); ?>>All</option>
			<?php
			//Get the sections
			$sections = get_categories(array('taxonomy' => WPCLNDR_CUSTOM_TAXONOMY));
			
			//If the section is not an array, we get the category information from the DB
			//$cur_section = isset($_GET['section']) ? $_GET['section'] : $section;
			$cur_section = is_string($section) && $section!='all' ? get_term_by('name', $section, WPCLNDR_CUSTOM_TAXONOMY) : is_numeric($section) ? get_term_by('id', WPCLNDR_CUSTOM_TAXONOMY) : $section;
			$cur_section = is_object($cur_section) ? $cur_section->term_id : $cur_section;
			
			//Loop through them
			foreach($sections as $section){
				//Generate an option element for each section
				?><option value="<?php echo $section->cat_ID; ?>"<?php echo ($cur_section==$section->cat_ID ? ' selected="selected"' : ''); ?>><?php echo $section->name; ?></option><?php
			}
			?>
		</select>
		<?php
			$start_formatted = empty($start) ? date(WPCLNDR_DATE_SLASHES_LEADING, strtotime('today')) : date(WPCLNDR_DATE_SLASHES_LEADING, strtotime(str_replace('-', '/', $start)));
			switch($view){
				case 'month':
					?>
					<label class="wpclndr-hidden" for="wpclndr-start-date">Month Of: </label>
					<input class="wpclndr-hidden" type="text" id="wpclndr-start-date" value="<?php echo $start_formatted; ?>" data-view="<?php echo $view; ?>" />
					<button id="wpclndr-find-events" type="button">Find Events</button>
					<?php
					break;
				case 'day':
					?>
					<label class="wpclndr-hidden" for="wpclndr-start-date">Day Of: </label>
					<input class="wpclndr-hidden" type="text" id="wpclndr-start-date" value="<?php echo $start_formatted; ?>" data-view="<?php echo $view; ?>" />
					<button id="wpclndr-find-events" type="button">Find Events</button>
					<?php
					break;
				case 'week':
					?>
					<label class="wpclndr-hidden" for="wpclndr-start-date">Week Of: </label>
					<input class="wpclndr-hidden" type="text" id="wpclndr-start-date" value="<?php echo $start_formatted; ?>" data-view="<?php echo $view; ?>" />
					<button id="wpclndr-find-events" type="button">Find Events</button>
					<?php
					break;
				case 'list':
				default:
					?>
					<label for="wpclndr-sort-select">Sort By: </label>
					<select id="wpclndr-sort-select">
						<option value="asc"<?php echo ($sort_by=='START' ? ' selected="selected"' : ''); ?>>Ascending Order</option>
						<option value="desc"<?php echo ($sort_by=='END' || empty($sort_by) ? ' selected="selected"' : ''); ?>>Descending Order</option>
					</select>
					<br />
					<label for="wpclndr-start-date">Start Date: </label>
					<input type="text" id="wpclndr-start-date" class="datepicker" value="<?php echo $start_formatted; ?>" data-view="<?php echo $view; ?>" data-date="<?php echo $start_formatted; ?>" />
					<label for="wpclndr-end-date">End Date: </label>
					<input type="text" id="wpclndr-end-date" class="datepicker" value="<?php echo !empty($end) ? date(WPCLNDR_DATE_SLASHES_LEADING, strtotime(str_replace('-', '/', $end))) : ''; ?>" data-view="<?php echo $view; ?>" data-date="<?php echo !empty($end) ? date(WPCLNDR_DATE_SLASHES_LEADING, strtotime(str_replace('-', '/', $end))) : ''; ?>" />
					<button id="wpclndr-find-events" type="button">Find Events</button>
					<button id="wpclndr-view-all" type="button">View All</button>
					<script type="text/javascript">
						jQuery(document).ready(function(){
							jQuery('#wpclndr-view-all').trigger('click');
						});
					</script>
					<?php
			}
		?>
</section>