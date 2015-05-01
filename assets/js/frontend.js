if (typeof(WPClndr)=="undefined") var WPClndr = {}; //Init the WPClndr object, if it is not already defined
if (typeof(WPClndr.events)=="undefined") WPClndr.events = {}; //Init the events, if it is not already defined
if (typeof(WPClndr.json)=="undefined") WPClndr.json = {}; //Init the json, if it is not already defined
WPClndr.cache = {}; //Init the cache object which will contain DOM objects
WPClndr.view = 'month'; //Init the view variable
WPClndr.animSpeed = 'medium'; //Init the default speed of JS animations

//Set the default datepicker options
WPClndr.defaults = {
				datepicker: {
								list: {
												dateFormat: 'm-d-yy',
												showButtonPanel: true
								},
								month: {
												dateFormat: 'm-yy',
												showButtonPanel: true
								},
								day: {
												dateFormat: 'm-d-yy',
												showButtonPanel: true
								},
								week: {
												dateFormat: 'm-d-yy',
												showButtonPanel: true
								}
				}
};

(function($){
    $(document).ready(function(){
								//Set the view if it is not set
								WPClndr.view = WPClndr.view=='' ? WPClndr.json.view : WPClndr.view;
								
								//Call init functions
								WPClndr.initLoadingAnims();
								WPClndr.initDOMCache();
								WPClndr.initDefaults();
								WPClndr.initToolbar();
								WPClndr.initContent();
								//WPClndr.set_address_bar();
								
								//Trigger the view select change event so that we have the right view on first load
								//WPClndr.cache.$toolbar.find('#wpclndr-view-select').trigger('change');
    });
				
				/*********************************************************
				 * Initializes the loading animation for use with ajax
				 ********************************************************/
				WPClndr.initLoadingAnims = function(){
								//Add CSS for animation to DOM
								$('body').append('<div id="wpclndr-overlay"></div><div id="wpclndr-ajax-loading-anim"><div id="wpclndr-css-anim-dots"><div id="wpclndr-css-anim-dots_1" class="wpclndr-css-anim-dots"></div><div id="wpclndr-css-anim-dots_2" class="wpclndr-css-anim-dots"></div><div id="wpclndr-css-anim-dots_3" class="wpclndr-css-anim-dots"></div></div></div>');
								
								//Set the Ajax animation to show and hide on Ajax start and stop
								$(document).ajaxStart(WPClndr.show_ajax_loader).ajaxStop(WPClndr.hide_ajax_loader);
				}
				
				/*********************************************************
				 * Initializes the toolbar for the calendar view
				 ********************************************************/
				WPClndr.initDOMCache = function(){
								//Cache initial DOM objects
								WPClndr.cache.$ajax_anim = $('#wpclndr-ajax-loading-anim');
								WPClndr.cache.$ajax_overlay = $('#wpclndr-overlay');
								WPClndr.cache.$toolbar = $('#wpclndr-header');
								WPClndr.cache.$events_container = $('#wpclndr-events-container');
								WPClndr.cache.$wpclndr_container = $('#wpclndr-container');
								
								//Init the overlay
								WPClndr.cache.$ajax_overlay.width($(window).width()).height($(window).height());
								
								//Set resize event for ajax animation							
								$(window).on('scroll', function(e){
												WPClndr.cache.$ajax_anim.css({ top: (($(window).height() - WPClndr.cache.$ajax_anim.outerHeight())/2)+window.scrollY, left: ($(window).width() - WPClndr.cache.$ajax_anim.outerWidth())/2, margin: -(parseInt(WPClndr.cache.$ajax_anim.height())/2) + 'px 0 0 ' + -(parseInt(WPClndr.cache.$ajax_anim.width())/2) + 'px' });
								}).trigger('scroll');
				}
				
				/*********************************************************
				 * Initializes the default objects
				 ********************************************************/
				WPClndr.initDefaults = function(){
								var obj = {
												"minDate": new Date(WPClndr.json.first_event.year, (WPClndr.json.first_event.month - 1), WPClndr.json.first_event.day)
								};
												
								//Init the datepicker minimum and maximum dates
								WPClndr.defaults.datepicker.list = obj;
								WPClndr.defaults.datepicker.day = obj;
								WPClndr.defaults.datepicker.week = obj;
								WPClndr.defaults.datepicker.month = obj;
				}
				
				/*********************************************************
				 * Initializes the toolbar for the calendar view
				 ********************************************************/
				WPClndr.initToolbar = function(){
								//Bind the view select box to change the calendar view and trigger it so we get the initial list
								WPClndr.cache.$toolbar.off('change', 'select#wpclndr-view-select').on('change', 'select#wpclndr-view-select', function(e){
												var option_val = $(this).find('option:selected').val();
												
												switch(option_val){
																case 'month':
																case 'day':
																case 'week':
																				var $start_date_obj = WPClndr.cache.$toolbar.find('input#wpclndr-start-date'),
																								start_date_array = $start_date_obj.val().split('-');
																				
																				//Show all applicable elements and hide ones not applicable to this view
																				WPClndr.cache.$toolbar.find('#wpclndr-sort-select').hide().prev('label').hide();
																				WPClndr.cache.$toolbar.find('#wpclndr-section-select').show().prev('label').show();
																				$start_date_obj.data('list_date', $start_date_obj.val())/*.val(start_date_array[0] + '-' + (typeof start_date_array[2]=='undefined' ? new Date().getFullYear() : start_date_array[2]))*/.hide().prev('label').hide();
																				WPClndr.cache.$toolbar.find('input#wpclndr-end-date').data('list_date', WPClndr.cache.$toolbar.find('input#wpclndr-end-date').val()).val('').hide().prev('label').hide();
																				WPClndr.cache.$toolbar.find('button#wpclndr-find-events').show();
																				WPClndr.cache.$toolbar.find('button#wpclndr-view-all-current').hide();
																				WPClndr.cache.$toolbar.find('button#wpclndr-view-all').hide();
																				WPClndr.cache.$toolbar.find('br').hide();
																				break;
																case 'list':
																default:
																				var $start_date_obj = WPClndr.cache.$toolbar.find('input#wpclndr-start-date'),
																								$end_date_obj = WPClndr.cache.$toolbar.find('input#wpclndr-end-date'),
																								start_date = $start_date_obj.data('list_date'),
																								end_date = $end_date_obj.data('list_date');
																				
																				//Show all applicable elements and hide ones not applicable to this view
																				WPClndr.cache.$toolbar.find('#wpclndr-sort-select').show().prev('label').show();
																				WPClndr.cache.$toolbar.find('#wpclndr-section-select').show().prev('label').show();
																				$start_date_obj/*.val(start_date)*/.show().prev('label').show();
																				$end_date_obj/*.val(end_date)*/.show().prev('label').show();
																				WPClndr.cache.$toolbar.find('button#wpclndr-find-events').show();
																				WPClndr.cache.$toolbar.find('button#wpclndr-view-all-current').show();
																				WPClndr.cache.$toolbar.find('button#wpclndr-view-all').show();
																				WPClndr.cache.$toolbar.find('br').show();
												}
												
												//Trigger the click method on the find events button so we get the newest events on select change
												WPClndr.cache.$toolbar.find('button#wpclndr-find-events').trigger('click');
								});
								
								//Bind change event to section select
								WPClndr.cache.$toolbar.off('change', 'select#wpclndr-section-select').on('change', 'select#wpclndr-section-select', function(){
												//Trigger the find event button
												WPClndr.cache.$toolbar.find('button#wpclndr-find-events').trigger('click');
								});
								
								//Bind change event to sort select
								WPClndr.cache.$toolbar.off('change', 'select#wpclndr-sort-select').on('change', 'select#wpclndr-sort-select', function(){
												//Trigger the find event button
												WPClndr.cache.$toolbar.find('button#wpclndr-find-events').trigger('click');
								});
								
								//Bind find events button
								WPClndr.cache.$toolbar.off('click', 'button#wpclndr-find-events').on('click', 'button#wpclndr-find-events', function(e){
												//Process dates
												var $start = WPClndr.cache.$toolbar.find('#wpclndr-start-date'),
																$end = WPClndr.cache.$toolbar.find('#wpclndr-end-date');
												
												//Make ajax call to WP Ajax system, which will route to the correct plugin function
												$.ajax({
																url: '/wp-admin/admin-ajax.php',
																type: 'post',
																data: {
																				action: 'wpclndr_display_events',
																				date_start: $start.val()=='' ? $start.data('date') : $start.val(),
																				date_end: $end.val()=='' ? $end.data('date') : $end.val(),
																				view: WPClndr.cache.$toolbar.find('select#wpclndr-view-select option:selected').val() || 'month',
																				sort: WPClndr.cache.$toolbar.find('select#wpclndr-sort-select option:selected').val() || 'START',
																				section: WPClndr.cache.$toolbar.find('select#wpclndr-section-select option:selected').val() || 'all'
																},
																dataType: 'html',
																error: function(errorThrown){
																				WPClndr.show_notification(errorThrown);
																},
																success:function(html){
																				//Remove the container div and add the html, containing the new events container, to the content div
																				WPClndr.cache.$wpclndr_container.empty().html(html);
																				
																				//Reinitialize the cache objects
																				WPClndr.initDOMCache();
																				WPClndr.initDefaults();
																				WPClndr.initToolbar();
																				WPClndr.initContent();
																}
												});
												
												e.preventDefault();
												return false;
								});
								
								//Bind view all button
								WPClndr.cache.$toolbar.off('click', 'button#wpclndr-view-all').on('click', 'button#wpclndr-view-all', function(e){
												//Make ajax call to WP Ajax system, which will route to the correct plugin function
												$.ajax({
																url: '/wp-admin/admin-ajax.php',
																type: 'post',
																data: {
																				action: 'wpclndr_display_events',
																				view_num: 'all',
																				view: WPClndr.cache.$toolbar.find('select#wpclndr-view-select option:selected').val(),
																				sort: WPClndr.cache.$toolbar.find('select#wpclndr-sort-select option:selected').val(),
																				section: WPClndr.cache.$toolbar.find('select#wpclndr-section-select option:selected').val()
																},
																dataType: 'html',
																error: function(errorThrown){
																				WPClndr.show_notification(errorThrown);
																},
																success:function(html){
																				//Remove the container div and add the html, containing the new events container, to the content div
																				WPClndr.cache.$wpclndr_container.empty().html(html);
																				
																				//Reinitialize the cache objects
																				WPClndr.initDOMCache();
																				WPClndr.initDefaults();
																				WPClndr.initToolbar();
																				WPClndr.initContent();
																}
												});
												
												e.preventDefault();
												return false;
								});
								
								//Init default datepicker options
								$.datepicker.setDefaults(WPClndr.defaults.datepicker.list);
								
								//Init datepickers
								WPClndr.cache.$toolbar.find('input[type="text"].datepicker').each(function(){
												var view = $(this).data('view');
												
												//Set the view
												WPClndr.view = view=='' ? WPClndr.view=='' ? WPClndr.json.view : WPClndr.view : view;
												
												//Init correct datepicker based on view
												switch(view){
																case 'month':
																				$(this).monthpicker({
																								pattern: 'm-yyyy',
																								selectedYear: new Date().getFullYear(),
																								startYear: WPClndr.json.first_event.year,
																								finalYear: WPClndr.json.last_event.year,
																								monthNames: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
																				});
																				break;
																case 'day':
																case 'week':
																case 'list':
																default:
																				//Load the defaults for the current view
																				$(this).removeClass('.hasDatepicker').datepicker(WPClndr.defaults.datepicker[view]).datepicker('refresh');
												}
												
												//Set the date if it is set in data
												if ($(this).data('date')!='' && $(this).data('date')!='undefined') $(this).val($(this).data('date'));
								});
				}
				
				/*********************************************************
				 * Initializes the content based on the view
				 ********************************************************/
				WPClndr.initContent = function(){
								//Make sure we have a view set
								WPClndr.view = typeof(WPClndr.view)=='undefined' ? 'month' : WPClndr.view;
								
								//Set the view
								switch(WPClndr.view){
												case 'month':
																//Init the full calendar view
																$('#wpclndr-events-view.wpclndr-events-view-month').fullCalendar({
																				allDayDefault: false,
																				eventSources: [{
																								events: WPClndr.events
																				}],
																				defaultView: 'month',
																				eventMouseover: WPClndr.eventMouseover,
																				eventMouseout: WPClndr.eventMouseout,
																				eventClick: WPClndr.eventClick,
																				dayClick: WPClndr.dayClick
																});
																
																//Explicitly change the view
																$('#wpclndr-events-view.wpclndr-events-view-month').fullCalendar('changeView', 'month');
																
																break;
												case 'day':
																//Init the full calendar view
																$('#wpclndr-events-view.wpclndr-events-view-day').fullCalendar({
																				allDayDefault: false,
																				eventSources: [{
																								events: WPClndr.events
																				}],
																				defaultView: 'agendaDay',
																				eventMouseover: WPClndr.eventMouseover,
																				eventMouseout: WPClndr.eventMouseout,
																				eventClick: WPClndr.eventClick,
																				dayClick: WPClndr.dayClick
																});
																
																//Explicitly change the view
																$('#wpclndr-events-view.wpclndr-events-view-day').fullCalendar('changeView', 'agendaDay');
																
																break;
												case 'week':
																//Init the full calendar view
																$('#wpclndr-events-view').fullCalendar({
																				allDayDefault: false,
																				eventSources: [{
																								events: WPClndr.events
																				}],
																				defaultView: 'agendaWeek',
																				eventMouseover: WPClndr.eventMouseover,
																				eventMouseout: WPClndr.eventMouseout,
																				eventClick: WPClndr.eventClick,
																				dayClick: WPClndr.dayClick
																});
																
																//Explicitly change the view
																$('#wpclndr-events-view.wpclndr-events-view-week').fullCalendar('changeView', 'agendaWeek');
																
																break;
												case 'list':
															//Check if we are directly linking to the list view
															/*if (window.location.href.search('view=list')>-1){
																		//Remove the query string from the location bar
																		window.location.href.replace('?view=list');
																		window.location.href.replace('view=list');
																		
																		//Load the events
																		$('#wpclndr-view-all').trigger('click');
															}*/
															
												default:
								}
				}
				
				/*********************************************************
				 * This function is triggered when the user mouses over
				 * an event on the calendar
				 ********************************************************/
				WPClndr.eventMouseover = function(event, jsEvent, view){
								var text = '', //Init the text to show in the speech bubble
												x = (typeof jsEvent.pageX=='undefined' || jsEvent.pageX==0 ? jsEvent.clientX : jsEvent.pageX), //x coordinates for the event
												y = (typeof jsEvent.pageY=='undefined' || jsEvent.pageY==0 ? jsEvent.clientY : jsEvent.pageY); //y coordinates for the event
								
								//Process the coordinates by taking the parent's coordinates and subtracting them
								//from the mouse coordinates. This allows us to position the speech bubble
								//relative to the events container, thereby allowing us to include the speech
								//bubble in the container's event bubbling
								x -= parseInt(WPClndr.cache.$events_container.offset().left);
								y -= parseInt(WPClndr.cache.$events_container.offset().top);
								
								//Generate the text inside of the speech bubble
								text += '<h3><a href="' + event.permalink + '" title="' + event.title + '">' + event.title + '</a></h3>'; //Generate the header
								text += '<h5>' + event.start_short + ' &mdash; ' + (event.start_short==event.end_short ? 'All Day' : event.end_short) + '</h5>'; //Generate date header
								text += '<p>' + (event.content.length>200 ? event.content.substr(0, 200) + '...' : event.content) + '</p>'; //Generate the content
								//text += '<a class="wpclndr-read-more" href="' + event.permalink + '" title="Read More...">Read More...</a>'; //Generate read more link
								
								//Show the speech bubble at the mouse cursor
								WPClndr.show_speech_bubble(text, x, y, false);
				}
				
				/*********************************************************
				 * This function is triggered when the user mouses off of
				 * an event on the calendar
				 ********************************************************/
				WPClndr.eventMouseout = function(event, jsEvent, view){
								WPClndr.hide_speech_bubble(false);
				}
				
				/*********************************************************
				 * Click event for calendar events
				 ********************************************************/
				WPClndr.eventClick = function(event, jsEvent, view){
								if (!(typeof event.permalink=='undefined' || event.permalink=='')) window.location = event.permalink;
				}
				
				/*********************************************************
				 * Click event for calendar days
				 ********************************************************/
				WPClndr.dayClick = function(date, allday, jsEvent, view){
								$('#wpclndr-events-view').fullCalendar('changeView', 'agendaDay');
								WPClndr.cache.$toolbar.find('#wpclndr-view-select option[selected="selected"]').removeAttr('selected');
								WPClndr.cache.$toolbar.find('#wpclndr-view-select option[value="day"]').attr('selected', 'selected');
								$('#wpclndr-events-view').fullCalendar('gotoDate', date);
				}
				
				/*********************************************************
				 * Shows a notification to the user
				 ********************************************************/
				WPClndr.show_notification = function(){
								
				}
				
				/*********************************************************
				 * Shows a loading animation
				 ********************************************************/
				WPClndr.show_ajax_loader = function(){
								//WPClndr.cache.$ajax_overlay.fadeIn(WPClndr.animSpeed, function(){
												WPClndr.cache.$ajax_anim.show();
								//});
				}
				
				/*********************************************************
				 * Hides a loading animation
				 ********************************************************/
				WPClndr.hide_ajax_loader = function(){
								//WPClndr.cache.$ajax_overlay.fadeOut(WPClndr.animSpeed);
								WPClndr.cache.$ajax_anim.hide();
				}
				
				/*********************************************************
				 * Shows a CSS3 speech bubble
				 ********************************************************/
				WPClndr.show_speech_bubble = function(text, x, y, animate){
								//Init the animate boolean if it wasn't passed (optional parameter)
								animate = (typeof animate=='undefined' || animate=='' ? false : animate);
								
								//Append the speech bubble to the body of the page and place it at the mouse cursor
								WPClndr.cache.$events_container.append('<div id="wpclndr-speech-bubble" class="wpclndr-speech-bubble" style="width: 400px;">' + text + '</div>');
								
								//Cache speech bubble
								var $speech_bubble = $('#wpclndr-speech-bubble');
								
								//Move the speech bubble to the correct coordinates
								$speech_bubble.css({ top: y, left: x });
								
								//Then show it
								if (animate) $speech_bubble.fadeIn(WPClndr.animSpeed); else $speech_bubble.show();
				}
				
				/*********************************************************
				 * Hides a CSS3 speech bubble
				 ********************************************************/
				WPClndr.hide_speech_bubble = function(animate){
								//Get the speech bubble
								var $speech_bubble = $('.wpclndr-speech-bubble');
								
								//Init the animate boolean if it wasn't passed (optional parameter)
								animate = (typeof animate=='undefined' || animate=='' ? false : animate);
								
								//Get the speech bubble and hide it
								if (animate) $speech_bubble.fadeOut(WPClndr.animSpeed).empty().remove(); else $speech_bubble.hide().empty().remove();
				}
				
				/*********************************************************
				 * Sets the address bar URL
				 ********************************************************/
				WPClndr.set_address_bar = function(){
								//Remove any query strings from the URL
								if (history.pushState){
												//Remove query string from URL
												url_array = location.href.split('?');
												
												//Set the new URL in the address bar
												history.pushState({}, document.title, url_array[0]);
								}
				}
}(jQuery));