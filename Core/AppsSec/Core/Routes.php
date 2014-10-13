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
		'route' => '/login',
		'ctrl' => 'Security',
		'action' => 'Login'
	]
];
