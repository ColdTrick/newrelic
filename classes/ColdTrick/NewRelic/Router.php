<?php

namespace ColdTrick\NewRelic;

/**
 * Route handling for named transactions
 *
 * @package    ColdTrick
 * @subpackage NewRelic
 */
class Router {
	
	/**
	 * Listen to all pages
	 *
	 * @param string $hook        the name of the hook
	 * @param string $type        the type of the hook
	 * @param array  $returnvalue current return value
	 * @param array  $params      supplied params
	 *
	 * @return void
	 */
	public static function pageListener($hook, $type, $returnvalue, $params) {
		
		if (!empty($returnvalue) && is_array($returnvalue)) {
			self::buildTransaction($returnvalue);
			return;
		}
		
		// only works in Elgg 1.10+
		if (!empty($params) && is_array($params)) {
			self::buildTransaction($params);
			return;
		}
		
		// ultimate fallback
		self::fallbackUrlHandling();
	}
	
	/**
	 * Build the named transaction based on an array of information from the route hook
	 *
	 * @param array $page_information the routing information
	 *
	 * @return void
	 */
	protected static function buildTransaction($page_information) {
		
		if (empty($page_information) || !is_array($page_information)) {
			return;
		}
		
		$transaction = [];
		
		$identifier = elgg_extract('identifier', $page_information);
		if (!empty($identifier)) {
			$transaction[] = $identifier;
		}
		
		// filter out usernames
		$usernames = self::getUsernamesToIgnore();
		
		$segments = elgg_extract('segments', $page_information);
		if (!empty($segments) && is_array($segments)) {
			foreach ($segments as $segment) {
				if (is_numeric($segment) || in_array($segment, $usernames)) {
					$transaction[] = '*';
					break;
				} else {
					$transaction[] = $segment;
				}
			}
		}
		
		newrelic_name_transaction('/' . implode('/', $transaction));
	}
	
	/**
	 * Get the usernames of page_owner and logged in user to ignore in named transactions
	 *
	 * @return array
	 */
	protected static function getUsernamesToIgnore() {
		$usernames = [];
		
		// check page owner
		$page_owner = elgg_get_page_owner_entity();
		if (elgg_instanceof($page_owner, 'user')) {
			$usernames[] = $page_owner->username;
		}
		
		// check logged in user
		$user = elgg_get_logged_in_user_entity();
		if (elgg_instanceof($user, 'user')) {
			$usernames[] = $user->username;
		}
		
		return $usernames;
	}
	
	/**
	 * Build the page elements for report based on the page URL
	 *
	 * @return void
	 */
	protected static function fallbackUrlHandling() {
		// get current page url
		$url = current_page_url();
		$path = parse_url($url, PHP_URL_PATH);
		
		// build the array for handling
		$segments = explode('/', trim($path, '/'));
		$identifier = array_shift($segments);
		
		$fallback = [
			'identifier' => $identifier,
			'segments' => $segments,
		];
		self::buildTransaction($fallback);
	}
}
