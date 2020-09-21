<?php

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('newrelic:settings:force_cli_end_transaction'),
	'#help' => elgg_echo('newrelic:settings:force_cli_end_transaction:help'),
	'name' => 'params[force_cli_end_transaction]',
	'value' => 1,
	'checked' => (bool) $plugin->force_cli_end_transaction,
	'switch' => true,
]);
