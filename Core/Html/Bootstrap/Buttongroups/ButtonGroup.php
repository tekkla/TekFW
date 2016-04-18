<?php
namespace Core\Html\Bootstrap\Buttongroups;

use Core\Html\Elements\Div;
use Core\Html\Form\Button;
use Core\Html\HtmlAbstract;
use Core\Html\HtmlException;

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
        /* @var $button \Core\Html\HtmlAbstract */
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
            Throw new HtmlException('Buttons for Bootstrap\ButtonGroups must have a set "btn" css class');
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

