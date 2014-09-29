<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Form\Select;
use Core\Lib\Content\Html\Form\Option;

/**
 * Creates a on/off switch control
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Controls
 * @license MIT
 * @copyright 2014 by author
 */
class OnOffSwitch extends Select
{
	use \Core\Lib\Traits\TextTrait;

	// array with option objects
	private $switch = [];

	// by deafult switch state is off eg 0
	private $state = 0;

	/**
	 * Factory Pattern
	 * @param string $name
	 * @return \Core\Lib\Content\Html\controls\OnOffSwitch
	 */
	public static function factory($name, $state = 0)
	{
		$obj = new self;
		$obj->setName($name);
		$obj->switchTo($state);
		return $obj;
	}

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Add on option
		$option = Option::factory();
		$option->setValue(1);
		$option->setInner($this->txt('on'));
		$this->switch['on'] = $option;

		// Add off option
		$option = Option::factory();
		$option->setValue(0);
		$option->setInner($this->txt('off'));
		$this->switch['off'] = $option;
	}

	/**
	 * Switches state to: on
	 */
	public function switchOn()
	{
		$this->switch['on']->isSelected(1);
		$this->switch['off']->isSelected(0);
		$this->state = 1;
	}

	/**
	 * Switches state to: off
	 */
	public function switchOff()
	{
		$this->switch['off']->isSelected(1);
		$this->switch['on']->isSelected(0);

		$this->state = 0;
	}

	/**
	 * Set switch to a specific state
	 * @param number $state
	 */
	public function switchTo($state)
	{
		$states = [
			0,
			1,
			false,
			true
		];

		if (!in_array($state, $states))
			Throw new \InvalidArgumentException('Wrong state for switch.', 1000);

		switch ($state)
		{
			case 0 :
			case false :
				$this->switchOff();
				break;
			case 1 :
			case true :
				$this->switchOn();
				break;
		}
	}

	/**
	 * Returns current switch state
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Core\Lib\Content\Html\Form\Select::build()
	 */
	public function build()
	{
		foreach ( $this->switch as $option )
			$this->addOption($option);

		return parent::build();
	}
}
