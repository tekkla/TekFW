<?php

namespace Apps\Test\Controller;

use Core\Lib\Amvc\Controller;

use Core\Lib\jQuery\jQuery;

/**
 *
 * @author
 * =>  =>  =>  =>  Michael
 *
 * =>  =>  =>  =>  "Tekkla"
 *
 * =>  =>  =>  =>  Zorn
 *
 * =>  =>  =>  =>  <tekkla@tekkla.d
 *
 */
class TestController extends Controller
{

	public $has_no_model;

	public function Index()
	{
			$this->jQuery('my_selector')html('my content')css('color', 'red')class('am-arsch');

			$response = jQuery::getResponse();

			$this->setVar('response', $response);


			$this->firelog($response);
	}
}

