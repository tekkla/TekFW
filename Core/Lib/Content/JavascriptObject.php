<?php
namespace Core\Lib\Content;

/**
 * Class for managing and creating of javascript objects
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class JavascriptObject
{

	/**
	 * Types can be "file", "script", "block", "ready" or "var".
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Header (false) or scripts (true) below body? This is the target for.
	 *
	 * @var bool
	 */
	private $defer = false;

	/**
	 * The script to add.
	 * This can be an url if its an file or a script block.
	 *
	 * @var string
	 */
	private $script;

	/**
	 * Flag for external files.
	 * External files wont be minified.
	 *
	 * @var bool
	 */
	private $is_external = false;

	/**
	 * Sets the objects type.
	 * Select from "file", "script", "ready", "block" or "var".
	 *
	 * @param string $type
	 * @throws Error
	 * @return \Core\Lib\Javascript
	 */
	public function setType($type)
	{
		$types = array(
			'file',
			'script',
			'ready',
			'block',
			'var'
		);

		if (! in_array($type, $types)) {
			Throw new \InvalidArgumentException('Javascript targets have to be "file", "script", "block", "var" or "ready"');
		}

		$this->type = $type;
		return $this;
	}

	/**
	 * Sets the objects external flag.
	 *
	 * @param bool $bool
	 * @return \Core\Lib\Javascript
	 */
	public function setIsExternal($bool)
	{
		$this->is_external = is_bool($bool) ? $bool : false;
		return $this;
	}

	/**
	 * Sets the objects script content.
	 *
	 * @param string $script
	 * @return \Core\Lib\Javascript
	 */
	public function setScript($script)
	{
		$this->script = $script;
		return $this;
	}

	/**
	 * Returns the objects type.
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/*
	 * + Returns the objects external flag state.
	 */
	public function getIsExternal()
	{
		return $this->is_external;
	}

	/**
	 * Returns the objects script content.
	 *
	 * @return string
	 */
	public function getScript()
	{
		return $this->script;
	}

	/**
	 * Sets the objects defer state.
	 *
	 * @param bool $defer
	 * @return \Core\Lib\Javascript
	 */
	public function setDefer($defer = false)
	{
		$this->defer = is_bool($defer) ? $defer : false;
		return $this;
	}

	/**
	 * Returns the objects defer state
	 *
	 * @return boolean
	 */
	public function getDefer()
	{
		return $this->defer;
	}
}