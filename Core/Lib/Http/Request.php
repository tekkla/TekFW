<?php
namespace Core\Lib\Http;

/**
 *
 * @author Michael
 *
 */
class Request
{

	public function __construct()
	{

	}

	public function getMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public function getURI()
	{
		return $_SERVER['REQUEST_URI'];
	}

}
