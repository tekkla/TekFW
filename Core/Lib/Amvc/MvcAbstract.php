<?php
namespace Core\Lib\Amvc;

/**
 * Abstract MVC class.
 *
 * Model, View and Controller libs are children of this class.
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2014
 * @license MIT
 */
abstract class MvcAbstract
{
	use \Core\Lib\Traits\StringTrait;
	use \Core\Lib\Traits\TextTrait;
	use \Core\Lib\Traits\AccessTrait;
	use \Core\Lib\Traits\AnalyzeVarTrait;

	/**
	 * Name of the MVC object
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Hold injected App object this MVC object is used for
	 *
	 * @var App
	 */
	public $app;

	/**
	 * MVC objects need an app instance.
	 *
	 * @param App $app
	 *
	 * @return \Core\Lib\Abstracts\MvcAbstract
	 */
	public function injectApp(App $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Sets the name of the MVC object
	 *
	 * @param string $name
	 *
	 * @return \Core\Lib\Abstracts\MvcAbstract
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Returns the name of the MVC object.
	 * Throws error when name is not set.
	 *
	 * @throws UnexpectedValueException
	 *
	 * @return string
	 */
	public function getName()
	{
		if (isset($this->name)) {
			return $this->name;
		}

		Throw new \UnexpectedValueException('Name from MVC component is not set.');
	}

	/**
	 * Returns the name of the App object insite the MVC object.
	 * Throws an error
	 * if the App object is not set.
	 *
	 * @throws Error
	 * @return string
	 */
	public function getAppName()
	{
		if (! isset($this->app)) {
			Throw new \UnexpectedValueException('MVC component has no set app name.');
		}

		return $this->app->getName();
	}
}
