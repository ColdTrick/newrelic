<?php

use ColdTrick\NewRelic\Bootstrap;

return [
	'bootstrap' => Bootstrap::class,
	'settings' => [
		'force_cli_end_transaction' => false,
	],
];
