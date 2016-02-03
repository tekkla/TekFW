<?php
namespace Core\Lib\Html\Bootstrap\Buttongroups;

use Core\Lib\Html\Elements\Div;
use Core\Lib\Html\Form\Button;
use Core\Lib\Html\HtmlAbstract;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;

class ButtonGroup extends Div
{

    private $buttons = [];

    protected $css = [
        'btn-group'
    ];

    protected $attributes = [
        'role' => 'group'
    ];

    /**
     * Creates a button element and adds it to the buttonlist.
     *
     * @return HtmlAbstract
     */
    public function &createButton($type = 'Form\Button')
    {
        /* @var $button \Core\Lib\Html\HtmlAbstract */
        $button = $this->factory->create($type);

        if (! $button instanceof Button && !$button->checkCss('btn')) {
            $button->addCss('btn');
        }

        return $this->addButton($button);
    }

    /**
     * Add a button element to the button list.
     *
     * @param Button $button
     *
     * @return HtmlAbstract
     */
    public function &addButton(HtmlAbstract $button)
    {
        if (! $button->checkCss('btn')) {
            Throw new InvalidArgumentException('Buttons for Bootstrap\ButtonGroups must have a set "btn" css class');
        }

        $uniqeid = uniqid('btngrp_btn');

        $this->buttons[$uniqeid] = $button;

        return $this->buttons[$uniqeid];
    }

    public function build()
    {
        foreach ($this->buttons as $button) {
            $this->inner .= $button->build();
        }

        return parent::build();
    }
}

