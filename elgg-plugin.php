<?php

use ColdTrick\NewRelic\Bootstrap;

return [
	'plugin' => [
		'version' => '2.1',
	],
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'force_cli_end_transaction' => false,
	],
];
