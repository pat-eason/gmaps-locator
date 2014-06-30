<?php
/*
Plugin Name: Google Maps Locator
Plugin URI: http://www.pateason.com
Description: Locator built on Google's Maps API to provide you with a searchable interface for locations. locations can be searched vie keyword or tags.
Version: 0.0.1
Author: Pat Eason
Author URI: http://www.pateason.com
License: MIT
*/

	if(!defined('ABSPATH')) {
		exit();
	}
	// GLOBAL PATHS

	/* =======================================================
		Define any global paths that you might
		want to use later on. This makes it easier to refer
		to paths and URLs that are relative to your plug-in.
		Feel free to add more.
	======================================================= */

	//this is the plug-in directory name
	if(!defined("GMAPS_LOCATOR")) {
		define("GMAPS_LOCATOR", trim(dirname(plugin_basename(__FILE__)), '/'));
	}

	//this is the path to the plug-in's directory
	if(!defined("GMAPS_LOCATOR_DIR")) {
		define("GMAPS_LOCATOR_DIR", WP_PLUGIN_DIR . '/' . GMAPS_LOCATOR);
	}

	//this is the url to the plug-in's directory
	if(!defined("GMAPS_LOCATOR_URL")) {
		define("GMAPS_LOCATOR_URL", WP_PLUGIN_URL . '/' . GMAPS_LOCATOR);
	}

	/* =======================================================
		Open /assets/classes/Plugin_Options.class.php
		and add any tables, options, or capabilities
		that you need added. This class has some methods
		for handling prefixing names and logging errors.
	======================================================= */

	// OPTIONS
	include_once(GMAPS_LOCATOR_DIR . '/assets/classes/GMAPS_LOCATOR_Options.class.php');

	/* =======================================================
		Open /assets/classes/GMAPS_LOCATOR.class.php
		and begin adding any functionality you need. This
		class has some default methods you may find useful
	======================================================= */

	//LOGIC
	include_once(GMAPS_LOCATOR_DIR . '/assets/classes/GMAPS_LOCATOR.class.php');

	/* =======================================================
		Any classes you add should extend the Plugin_Options_Name
		class so that you have access to the prefixing and logging methods.
	======================================================= */

	if(class_exists('GMAPS_LOCATOR')) {
		$GMAPS_LOCATOR = new GMAPS_LOCATOR();
		register_activation_hook(__FILE__, array($GMAPS_LOCATOR, 'activate'));
		register_deactivation_hook(__FILE__, array($GMAPS_LOCATOR, 'deactivate'));
	}
