<?php

namespace ColdTrick\NewRelic;

use Elgg\DefaultPluginBootstrap;
use Elgg\Router\Route;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::init()
	 */
	public function init() {
		
		if (!$this->isAvailable()) {
			return;
		}
		
		$this->logTransaction();
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
	 * Get the configured route for this page
	 *
	 * @return void|Route
	 */
	protected function getRoute() {
		
		$matcher = _elgg_services()->urlMatcher;
		$request = _elgg_services()->request;
		
		try {
			$params = $matcher->matchRequest($request);
			
			$route = _elgg_services()->routes->get($params['_route']);
			$route->setMatchedParameters($params);
			
			return $route;
		} catch (\Exception $e) {}
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
		$route = $this->getRoute();
		if ($route instanceof Route) {
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
			$path = parse_url(current_page_url(),  PHP_URL_PATH);
		}
		
		newrelic_name_transaction($path);
	}
}
