<?php
namespace Core\Lib\Html\Controls;

use Core\Lib\Html\Form\Select;
use Core\Lib\Language\TextTrait;
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

    private function createSwitches()
    {
        if (! empty($this->switch)) {
            return;
        }

        // Add off option
        $option = $this->factory->create('Form\Option');

        $option->setValue(0);
        $option->setInner($this->text('states.off'));

        $this->switch['off'] = $option;

        // Add on option
        $option = $this->factory->create('Form\Option');

        $option->setValue(1);
        $option->setInner($this->text('states.on'));

        $this->switch['on'] = $option;
    }

    /**
     * Switches state to: on
     */
    public function switchOn()
    {
        $this->createSwitches();

        $this->switch['on']->isSelected();
        $this->switch['off']->notSelected();

        $this->setValue(1);
    }

    /**
     * Switches state to: off
     */
    public function switchOff()
    {
        $this->createSwitches();

        $this->switch['on']->notSelected();
        $this->switch['off']->isSelected();

        $this->setValue(0);
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
            true,
            'on',
            'off',
            'yes',
            'no'
        ];

        if (! in_array($state, $states)) {
            Throw new InvalidArgumentException('Wrong state for on/off switch.');
        }

        $this->createSwitches();

        switch ($state) {
            case 0:
            case false:
            case 'off':
            case 'no':
                $this->switchOff();
                break;
            case 1:
            case true:
            case 'on':
            case 'yes':
                $this->switchOn();
                break;
        }
    }

    /**
     * Returns current switch state
     */
    public function getState()
    {
        return $this->getValue();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Html\Form\Select::build()
     */
    public function build()
    {
        $this->createSwitches();

        /* @var $option \Core\Lib\Html\Form\Option */
        foreach ($this->switch as $option) {

            $value = $option->getValue();

            if (! $value) {
                $value = $option->getInner();
            }

            if ($this->getValue() == $value) {
                $option->isSelected();
            }

            $this->addOption($option);
        }

        return parent::build();
    }
}
