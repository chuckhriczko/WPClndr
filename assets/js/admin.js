var WPClndr_Admin = {}; //Init the primary WPClndr admin object

(function($) {
    $(document).ready(function(){
        var $metabox = $('div#wpclndr-add-meta-box-create-event'); //Cache the metabox for faster DOM access
        
								//Init the datepickers
        $metabox.find('.datepicker').datepicker(jQuery.extend({}, {
																dateFormat: "mm/dd/yy",
																showOn: 'both',
																buttonImage: WPClndr.json.site_info.plugin_url + 'assets/images/calendar.gif',
																buttonImageOnly: true,
																defaultDate: "+1w",
																changeMonth: true,
																numberOfMonths: 3
												}, {
            onSelect: function(selectedDate) {
                if ($(this).prop('id')=='wpclndr_start_date') $("#wpclndr_end_date").datepicker("option", "minDate", selectedDate);
            }
        }));
        
        //Set up the timepickers
        $metabox.find('input[type="text"].timepicker').timepicker({ 'scrollDefaultNow': true });
        
        //Bind click event for all day checkbox
        $metabox.on('click', 'input[type="checkbox"]#wpclndr_allday', function(){
            //If the checkbox is clicked, then we hide the timepickers and their labels since it would be all day
            //If it is unchecked, then we show the above
            if ($(this).is(':checked')) $metabox.find('div.inside').find('input[type="text"].timepicker').parent('div').addClass('hidden'); else $metabox.find('div.inside').find('input[type="text"].timepicker').parent('div').removeClass('hidden');
        });
								
								//Remove areas from the wordpress admin
								$('select[name="seo_filter"], div#revisionsdiv, #wpseo_meta').hide();
								
								//Cache DOM elements
								var $recurring = $('#wpclndr_recurring'),
												$recurring_days_container = $recurring.next();

								//Show the recurring textbox if the checkbox is already checked
								if ($recurring.is(':checked')){
												$recurring_days_container.css({ height: 100 });
								} else {
												$recurring_days_container.css({ height: 0 });
								}
								
								//Bind the recurring checkbox
								$recurring.on('click', function(){
												if ($recurring.is(':checked')){
																$recurring_days_container.animate({ height: 100 }, 'medium');
												} else {
																$recurring_days_container.animate({ height: 0 }, 'medium');
												}
								});
    });
}(jQuery));