<?php

namespace Apps\Test;

use Core\Lib\Amvc\App;
use Core\Lib\jQuery\jQuery;

/**
 *
 * @author
 * =>  =>  =>  =>  Michael
 *
 *
 * =>  =>  =>  =>  "Tekkla"
 *
 *
 * =>  =>  =>  =>  Zorn
 *
 *
 * =>  =>  =>  =>  <tekkla@tekkla.d
 *
 */
class Test extends App
{
	public $routes = array(
			array(
					'name' => 'test_index',
					'route' => '/',
					'ctrl' => 'test',
					'action' => 'index'
			)
	);
}

