<?php
/**
 * Settingsfile for secured app Admin
 */
if (!defined('TEKFW'))
	die('Can not run without CoreFramework.');


return 	[
	'flags' => [
		'secure',
		'lang',
	],
	'routes' => [
		[
			'name' => 'index',
			'route' => '/',
			'ctrl' => 'admin',
			'action' => 'index'
		],
		[
			'name' => 'app_install',
			'route' => '/[a:app_name]/install',
			'ctrl' => 'config',
			'action' => 'install'
		],
		[
			'name' => 'app_remove',
			'route' => '/[a:app_name]/remove',
			'ctrl' => 'config',
			'action' => 'remove'
		],
		[
			'name' => 'app_config',
			'method' => 'GET|POST',
			'route' => '/[a:app_name]/config',
			'ctrl' => 'config',
			'action' => 'config'
		],
		[
			'name' => 'app_reconfig',
			'route' => '/[a:app_name]/reconfig',
			'ctrl' => 'config',
			'action' => 'reconfigure'
		]
	]
];
