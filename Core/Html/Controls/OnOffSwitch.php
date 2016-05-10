<?php
namespace Core\Html\Controls;

use Core\Html\Form\Select;
use Core\Html\HtmlException;
use Core\Html\HtmlBuildableInterface;

/**
 * OnOffSwitch.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class OnOffSwitch implements HtmlBuildableInterface
{
    /**
     *
     * @var Select
     */
    public $html;

    /**
     *
     * @var array
     */
    private $strings = [
        'on' => 'on',
        'off' => 'off'
    ];

    // array with option objects
    private $switch = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->html = new Select();
    }

    /**
     * Sets the string for the on state switch
     *
     * @param string $on
     */
    public function setOnString($on)
    {
        $this->strings['on'] = $on;
    }

    /**
     * Sets the string for the off state switch
     *
     * @param string $off
     */
    public function setOffString($off)
    {
        $this->strings['off'] = $off;
    }

    private function createSwitches()
    {
        if (! empty($this->switch)) {
            return;
        }

        // Add off option
        $option = $this->factory->create('Form\Option');

        $option->setValue(0);
        $option->setInner($this->strings['off']);

        $this->switch['off'] = $option;

        // Add on option
        $option = $this->factory->create('Form\Option');

        $option->setValue(1);
        $option->setInner($this->strings['on']);

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

        $this->html->setValue(1);
    }

    /**
     * Switches state to: off
     */
    public function switchOff()
    {
        $this->createSwitches();

        $this->switch['on']->notSelected();
        $this->switch['off']->isSelected();

        $this->html->setValue(0);
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
            Throw new HtmlException('Wrong state for on/off switch.');
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
     * {@inheritDoc}
     * @see \Core\Html\Form\Select::build()
     */
    public function build()
    {
        $this->createSwitches();

        /* @var $option \Core\Html\Form\Option */
        foreach ($this->switch as $option) {

            $value = $option->getValue();

            if (! $value) {
                $value = $option->getInner();
            }

            if ($this->getValue() == $value) {
                $option->isSelected();
            }

            $this->html->addOption($option);
        }

        return $this->html->build();
    }
}
