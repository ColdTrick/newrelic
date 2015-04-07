<?php
/**
 * The main plugin file
 */

// Only work if the New Relic php agent is loaded
if (extension_loaded('newrelic')) {
	// only work when the correct php module is loaded
	elgg_register_event_handler('init', 'system', 'newrelic_init');
}

/**
 * Called during system init
 *
 * @return void
 */
function newrelic_init() {
	
	elgg_register_plugin_hook_handler('route', 'all', array('\ColdTrick\NewRelic\Router', 'pageListener'), 99999);
}