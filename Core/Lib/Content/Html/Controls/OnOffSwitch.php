<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Form\Select;
use Core\Lib\Traits\TextTrait;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * OnOffSwitch.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class OnOffSwitch extends Select
{
    use TextTrait;

    // array with option objects
    private $switch = [];

    // by deafult switch state is off eg 0
    private $state = 0;

    private function createSwitches()
    {
        if (! empty($this->switch)) {
            return;
        }

        // Add off option
        $option = $this->factory->create('Form\Option');

        $option->setValue(0);
        $option->setInner($this->txt('off'));

        $this->switch['off'] = $option;

        // Add on option
        $option = $this->factory->create('Form\Option');

        $option->setValue(1);
        $option->setInner($this->txt('on'));

        $this->switch['on'] = $option;
    }

    /**
     * Switches state to: on
     */
    public function switchOn()
    {
        $this->createSwitches();

        $this->switch['on']->isSelected(1);
        $this->switch['off']->isSelected(0);
        $this->state = 1;
    }

    /**
     * Switches state to: off
     */
    public function switchOff()
    {
        $this->createSwitches();

        $this->switch['off']->isSelected(1);
        $this->switch['on']->isSelected(0);

        $this->state = 0;
    }

    /**
     * Set switch to a specific state
     *
     * @param number $state
     *
     * @throws InvalidArgumentException
     *
     * @return OnOffSwitch
     */
    public function switchTo($state)
    {
        $states = [
            0,
            1,
            false,
            true
        ];

        if (! in_array($state, $states)) {
            Throw new InvalidArgumentException('Wrong state for on/off switch.');
        }

        $this->createSwitches();

        switch ($state) {
            case 0:
            case false:
                $this->switchOff();
                break;
            case 1:
            case true:
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
     *
     * @see \Core\Lib\Content\Html\Form\Select::build()
     */
    public function build()
    {
        $this->createSwitches();

        foreach ($this->switch as $option) {
            $this->addOption($option);
        }

        return parent::build();
    }
}
