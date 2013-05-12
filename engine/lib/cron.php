<?php
/**
 * Elgg cron library.
 *
 * @package Elgg
 * @subpackage Core
 */

/**
 * Cron initialization
 *
 * @return void
 * @access private
 */
function _elgg_cron_init() {
	elgg_register_page_handler('cron', '_elgg_cron_page_handler');

	elgg_register_plugin_hook_handler('public_pages', 'walled_garden', '_elgg_cron_public_pages');

	elgg_set_config('elgg_cron_periods', array(
		'minute',
		'fiveminute',
		'fifteenmin',
		'halfhour',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'reboot',
	));
}

/**
 * Cron handler
 *
 * @param array $page Pages
 *
 * @return bool
 * @throws CronException
 * @access private
 */
function _elgg_cron_page_handler($page) {
	if (!isset($page[0])) {
		forward();
	}

	$period = strtolower($page[0]);

	$allowed_periods = 	elgg_get_config('elgg_cron_periods');

	if (!in_array($period, $allowed_periods)) {
		throw new CronException("$period is not a recognized cron period.");
	}

	// Get a list of parameters
	$params = array();
	$params['time'] = time();

	// Data to return to
	$old_stdout = "";
	ob_start();

	$old_stdout = elgg_trigger_plugin_hook('cron', $period, $params, $old_stdout);
	$std_out = ob_get_clean();

	echo $std_out . $old_stdout;
	return true;
}

/**
 * Register cron's pages as public in case we're in Walled Garden mode
 *
 * @param string $hook   'public_pages'
 * @param string $type   'walled_garden'
 * @param array  $pages  Array of pages to allow
 * @param mixed  $params Params
 *
 * @return array
 * @access private
 */
function _elgg_cron_public_pages($hook, $type, $pages, $params) {

	$periods = 	elgg_get_config('elgg_cron_periods');
	foreach ($periods as $period) {
		$pages[] = "cron/$period";
	}

	return $pages;
}

elgg_register_event_handler('init', 'system', '_elgg_cron_init');
