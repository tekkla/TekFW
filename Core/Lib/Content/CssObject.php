<?php
namespace Core\Lib\Content;

/**
 * CssObject
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
final class CssObject
{

	/**
	 * Type of css object
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Css object content
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Sets objects type.
	 * Type can be "file" or "inline".
	 *
	 * @param string $type
	 *
	 * @throws Error
	 *
	 * @return \Core\Lib\Css
	 */
	public function setType($type)
	{
		$types = [
			'file',
			'inline'
		];

		if (! in_array($type, $types)) {
			Throw new \InvalidArgumentException('Css type must be "inline" or "file".');
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * Sets objects css content.
	 *
	 * @param string $value
	 *
	 * @return \Core\Lib\Css
	 */
	public function setCss($value)
	{
		$this->content = $value;

		return $this;
	}

	/**
	 * Get objects type (file or inline)
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get objects css content
	 *
	 * @return string
	 */
	public function getCss()
	{
		return $this->content;
	}
}