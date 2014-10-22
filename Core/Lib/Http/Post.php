<?php
namespace Core\Lib\Http;

use Core\Lib\Data\Data;

/**
 *
 * @author Michael
 *
 */
class Post
{

	/**
	 * Storage for POST values
	 *
	 * @var Data
	 */
	private $post = false;

	/**
	 * Storage for unprocessed POST values
	 *
	 * @var Data
	 */
	private $raw = false;


	private $processed = false;


	public function get() {
		return $this->post;
	}

	public function raw()
	{
		return $this->raw;
	}

	public function process()
	{
		// Finally try to process possible posted data
		if (! empty($_POST)) {
			$this->post = new Data($_POST['app']);
			$this->post_raw = $_POST;
		}
	}
}
