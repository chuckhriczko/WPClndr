<?php
global $wpdb;

//Plugin Specific Constants
define('WPCLNDR_DEFAULT_LOCATION', '4444 Walbert Avenue, Allentown, PA. 18104');
define('WPCLNDR_PLUGIN_NAME', 'WPClndr');
define('WPCLNDR_CUSTOM_POST_TYPE', 'wpclndr');
define('WPCLNDR_CUSTOM_TAXONOMY', 'wpclndr_cat');
define('WPCLNDR_CUSTOM_TAXONOMY_DEFAULT', 'Section');
define('WPCLNDR_URI_SEGMENT', 'calendar');

//DB Tables
define('WPCLNDR_DB_TABLE_EVENTS', $wpdb->prefix.'wpclndr_events');

//Date Constants
define('WPCLNDR_DATE_MYSQL', 'Y-m-d H:i:s');
define('WPCLNDR_DATE_MYSQL_NOTIME', 'Y-m-d');
define('WPCLNDR_DATE_SHORT', 'M jS, Y');
define('WPCLNDR_DATE_LONG', 'F j, Y');
define('WPCLNDR_DATE_DASHES', 'n-j-Y');
define('WPCLNDR_DATE_SLASHES', 'n/j/Y');
define('WPCLNDR_DATE_DASHES_LEADING', 'm-d-Y');
define('WPCLNDR_DATE_SLASHES_LEADING', 'm/d/Y');

//Date/Time Constants
define('WPCLNDR_DATE_TIME_SHORT', 'M jS, Y g:i A');
define('WPCLNDR_DATE_TIME_LONG', 'F j, Y g:i A');

//Time Constants
define('WPCLNDR_TIME_MYSQL_NODATE', 'H:i:s');
define('WPCLNDR_TIME_TWELVE', 'g:i A');
define('WPCLNDR_TIME_TWENTYFOUR', 'H:i');

//String Constants
define('WPCLNDR_STRING_LENGTH_ELLIPSIS', 500);

//Unicode Character/HTML Entity Constants
define('WPCLNDR_SYMBOL_ARROW_DOWN', '&#9660;');
define('WPCLNDR_SYMBOL_ARROW_UP', '&#9650;');

//Template Constants
define('WPCLNDR_DEFAULT_TEMPLATE_PREFIX', 'wpclndr-');
define('WPCLNDR_DEFAULT_TEMPLATE', 'calendar-list');
define('WPCLNDR_DEFAULT_SINGLE_TEMPLATE', WPCLNDR_DEFAULT_TEMPLATE_PREFIX.'event');
?>