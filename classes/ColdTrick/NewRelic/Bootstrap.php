<?php

namespace ColdTrick\NewRelic;

use Elgg\DefaultPluginBootstrap;
use Elgg\Router\Route;
use Elgg\Application;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function boot() {
		
		if (!$this->isAvailable() || !Application::isCli()) {
			return;
		}
		
		// tell new relic this is a CLI / background call
		newrelic_background_job(true);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function init() {
		
		if (!$this->isAvailable()) {
			return;
		}
		
		$this->logTransaction();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function shutdown() {
		
		if (!$this->isAvailable()) {
			return;
		}
		
		if (!Application::isCli() || !(bool) $this->plugin()->force_cli_end_transaction) {
			return;
		}
		
		// stop transaction timer
		newrelic_end_of_transaction();
		// send all gathered data to the daemon
		newrelic_end_transaction();
		
		// wait for the daemon to send all data
		// it does this every minute
		set_time_limit(0);
		sleep(60);
	}
	
	/**
	 * Is the PHP extension for NewRelic loaded
	 *
	 * @return bool
	 */
	protected function isAvailable() {
		return extension_loaded('newrelic');
	}
	
	/**
	 * Set the current transaction name
	 *
	 * @return void
	 */
	protected function logTransaction() {
		
		if (!$this->isAvailable()) {
			return;
		}
		
		$path = null;
		$route = elgg_get_current_route();
		if (Application::isCli()) {
			$path = 'cli';
			// try to find cli command
			if (substr($_SERVER['SCRIPT_NAME'], -8) === 'elgg-cli') {
				$path = 'elgg-cli';
				$command = _elgg_services()->cli_input->getFirstArgument();
				if (!empty($command)) {
					$path .= ":{$command}";
				}
			}
		} elseif ($route instanceof Route) {
			$path = $route->getPath();
			
			// log route params
			$params = $route->getMatchedParameters();
			foreach ($params as $name => $value) {
				if (strpos($name, '_') === 0) {
					// some params are used for internals, they start with '_'
					continue;
				}
				
				newrelic_add_custom_parameter('route:param:' . $name, $value);
			}
			
			// log the route name
			newrelic_add_custom_parameter('route:name', $route->getName());
		} else {
			$path = parse_url(elgg_get_current_url(), PHP_URL_PATH);
		}
		
		newrelic_name_transaction($path);
	}
}
