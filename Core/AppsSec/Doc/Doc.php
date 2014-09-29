<?php
namespace Core\AppsSec\Doc;

use Core\Lib\Amvc\App;

// Check for direct file access
if (!defined('TEKFW'))
	die('Cannot run without TekFW framework...');

/**
 * Mainclass for secured Doc app
 * This app handles all admin stuff about the framework and apps
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package Doc (AppSec)
 * @subpackage Main
 */
class Doc extends App
{
	public $secure = true;
	public $css = true;
	#public $js = true;
	public $lang = true;
	public $routes = array(
		array(
			'name' => 'url',
			'route' => '/',
			'ctrl' => 'Main',
			'action' => 'Index'
		),
		array(
			'method' => 'GET|POST',
			'name' => 'docedit',
			'route' => '/[i:id_document]?/edit',
			'ctrl' => 'Document',
			'action' => 'Edit'
		)
	);
}

