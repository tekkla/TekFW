<?php
namespace Apps\Test\Controller;

use Core\Lib\Amvc\Controller;

/**
 *
 * @author michael
 *        
 */
class TestController extends Controller
{

	public $has_no_model;

	public function Index()
	{
		echo 'Test Controller';
	}
}

