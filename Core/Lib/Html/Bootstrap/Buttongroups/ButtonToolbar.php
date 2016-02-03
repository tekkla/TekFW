<?php
namespace Core\Lib\Html\Bootstrap\Buttongroups;

use Core\Lib\Html\Elements\Div;

class ButtonToolbar extends Div
{
    private $groups = [];

    protected $css = [
        'btn-toolbar'
    ];

    protected $attributes = [
        'role' => 'toolbar',
    ];

    /**
     * Creates a ButtonGroup object, adds it to the groups list and returns a reference to it
     *
     * @return Core\Lib\Html\Bootstrap\Buttongroups\ButtonGroup
     */
    public function &createButtongroup()
    {
        return $this->addButtongroup($this->factory->create('Bootstrap\Buttongroups\ButtonGroup'));
    }

    /**
     * Adds a ButtonGroup object to the groups list and returns reference to it.
     *
     * @return Core\Lib\Html\Bootstrap\Buttongroups\ButtonGroup
     */
    public function &addButtongroup(ButtonGroup $buttongroup) {

        $uniqeid = uniqid('btntoolbar_btngrp_');
        $this->groups[$uniqeid] = $buttongroup;

        return $this->groups[$uniqeid];
    }

    public function build()
    {
        foreach ($this->groups as $group) {
            $this->inner.= $group->build();
        }

        return parent::build();
    }
}
