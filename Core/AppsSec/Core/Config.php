<?php
if (! defined('TEKFW'))
	die('Can not run without CoreFramework.');

return [

	// Group: Global
	'default_action' => [
		'group' => 'global',
		'control' => 'input',
		'default' => 'forum'
	],
	'default_app' => [
		'group' => 'global',
		'control' => 'input'
	],
	'default_ctrl' => [
		'group' => 'global',
		'control' => 'input'
	],
	'content_handler' => [
		'group' => 'global',
		'control' => 'input'
	],
	'menu_handler' => [
		'group' => 'global',
		'control' => 'input'
	],

	// Group: JS
	'js_default_position' => [
		'group' => 'js',
		'control' => 'select',
		'data' => [
			'array',
			[
				't' => 'Top',
				'b' => 'Bottom'
			],
			0
		],
		'default' => 'top',
		'translate' => 'false'
	],
	'jquery_version' => [
		'group' => 'js',
		'control' => 'input',
		'default' => '1.11.1',
		'translate' => false
	],
	'js_html5shim' => [
		'group' => 'js',
		'control' => 'switch',
		'default' => 0
	],
	'js_modernizr' => [
		'group' => 'js',
		'control' => 'switch',
		'default' => 0
	],
	'js_selectivizr' => [
		'group' => 'js',
		'control' => 'switch',
		'default' => 0
	],
	'js_fadeout_time' => [
		'group' => 'js',
		'control' => 'number',
		'default' => 5000
	],

	// Group: Minify
	'css_minify' => [
		'group' => 'minify',
		'control' => 'switch',
		'default' => 0
	],
	'js_minify' => [
		'group' => 'minify',
		'control' => 'switch',
		'default' => 0
	],

	// Bootstrap
	'bootstrap_version' => [
		'group' => 'style',
		'control' => 'input',
		'default' => '3.1.1',
		'translate' => false
	],

	'fontawesome_version' => [
		'group' => 'style',
		'control' => 'input',
		'default' => '4.0.3',
		'translate' => false
	],

	// Logging
	'log' => [
		'group' => 'logging',
		'control' => 'switch',
		'default' => 0
	],
	'show_log_output' => [
		'group' => 'logging',
		'control' => 'switch',
		'default' => 1
	],
	'log_db' => [
		'group' => 'logging',
		'control' => 'switch',
		'default' => 1
	],
	'log_app' => [
		'group' => 'logging',
		'control' => 'switch',
		'default' => 1
	],
	'log_handler' => [
		'group' => 'logging',
		'control' => 'select',
		'data' => [
			'array',
			[
				'page' => 'Page',
				'fire' => 'FirePHP'
			],
			0
		],
		'default' => 'page',
		'translate' => false
	],

	// Url related
	'url_seo' => [
		'group' => 'url',
		'control' => 'switch',
		'default' => 0
	]
];
