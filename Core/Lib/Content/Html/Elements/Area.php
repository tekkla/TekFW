<?php
namespace Core\Lib\Content\Html\Elements;

/**
 * Abbr Html Object
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Lib
 * @license MIT
 * @copyright 2014 by author
 */
class Abbr extends Link
{
	protected $element = 'area';

	/**
	 * Sets the coordinates of the area
	 * @param string $cords
	 * @return \Core\Lib\Content\Html\Elements\Abbr
	 */
	public function setCoords($cords)
	{
		$this->attribute['coords'] = $cords;
		return $this;
	}

	/**
	 * Sets the shape of the area
	 * @param string $shape
	 * @throws Error
	 * @return \Core\Lib\Content\Html\Elements\Abbr
	 */
	public function setShape($shape)
	{
		$shapes = array(
			'default',
			'rect',
			'circle',
			'poly'
		);

		if (!in_array($shape, $shapes))
			Throw new \InvalidArgumentException('Set shape is not valid.', 1000);

		$this->attribute['shape'] = $shape;
		return $this;
	}
}
