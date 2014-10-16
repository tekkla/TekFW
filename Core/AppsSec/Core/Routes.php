<?php
if (! defined('TEKFW'))
	die('Can not run without CoreFramework.');

return [
	[
		'name' => 'index',
		'route' => '../',
		'ctrl' => 'Index',
		'action' => 'Index'
	],
	[
		'name' => 'login',
		'method' => 'GET|POST',
		'route' => '../login',
		'ctrl' => 'Security',
		'action' => 'Login'
	],
	[
		'name' => 'admin',
		'route' => '../admin',
		'ctrl' => 'admin',
		'action' => 'index'
	],
	[
		'name' => 'install',
		'route' => '../admin/[a:app_name]/install',
		'ctrl' => 'config',
		'action' => 'install'
	],
	[
		'name' => 'remove',
		'route' => '../admin/[a:app_name]/remove',
		'ctrl' => 'config',
		'action' => 'remove'
	],
	[
		'name' => 'config',
		'method' => 'GET|POST',
		'route' => '../admin/[a:app_name]/config',
		'ctrl' => 'config',
		'action' => 'config'
	],
	[
		'name' => 'reconfig',
		'route' => '../admin/[a:app_name]/reconfig',
		'ctrl' => 'config',
		'action' => 'reconfigure'
	]
];
