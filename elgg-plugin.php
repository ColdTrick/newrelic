<?php

use ColdTrick\NewRelic\Bootstrap;

return [
	'plugin' => [
		'version' => '3.0',
	],
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'force_cli_end_transaction' => false,
	],
];
