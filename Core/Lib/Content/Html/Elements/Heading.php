<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

/**
 * Creates a heading html object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Element
 * @license MIT
 * @copyright 2014 by author
 */
class Heading extends HtmlAbstract
{
	/**
	 * Size of heading.
	 * Default: 1
	 * @var int
	 */
	private $size = 1;

	/**
	 * Creates an ready to use object with the set size
	 * @param size $number
	 * Size of heading. Default: 1
	 * @return \Core\Lib\Content\Html\Elements\Heading
	 */
	public static function factory($size = 1)
	{
		return new Heading($size);
	}

	/**
	 * Constructor
	 * @param unknown $size
	 */
	public function __construct($size = 1)
	{
		$this->setSize($size);
		$this->element('h' . $size);
	}

	public function setSize($size)
	{
		$sizes = array(
			1,
			2,
			3,
			4,
			5,
			6
		);

		if (!in_array((int) $size, $sizes))
			Throw new \InvalidArgumentException('Wrong size set.', 1000);

		$this->size = $size;
	}
}
