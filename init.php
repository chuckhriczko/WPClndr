<?php
/*******************************************************************************
 * Obligatory WordPress plugin information
 ******************************************************************************/
/*
Plugin Name: WPClndr
Plugin URI: http://objectunoriented.com/projects/wpclndr
Description: Wordpress plugin that provides event management with a built in calendar
Version: 2.0
Author: Chuck Hriczko
Author URI: http://objectunoriented.com
License: GPLv2
*/
/*******************************************************************************
 * Require necessary files
 ******************************************************************************/
require_once('lib/constants.php');
require_once('lib/wpclndr.class.php');
require_once('lib/wpclndr_model.class.php');

/*******************************************************************************
 * Instantiate our class
 ******************************************************************************/
$wpclndr = new WPClndr(); //Initialize the WPClndr class
?>