<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Content\Html\HtmlAbstract;

/**
 * Creates a heading html object
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
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
	 *
	 * @var int
	 */
	private $size = 1;

	protected $element = 'h1';

	public function setSize($size)
	{
		$sizes = [
			1,
			2,
			3,
			4,
			5,
			6
		];

		if (! in_array((int) $size, $sizes)) {
			Throw new \InvalidArgumentException('Size "' . $size . '" is not an allowed size for heading html elements');
		}

		$this->element = 'h' . $size;
	}
}
