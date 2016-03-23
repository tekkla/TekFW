<?php
namespace Core\Lib\Html\Bootstrap\Panel;

use Core\Lib\Html\Elements\Div;

class PanelHeading extends PanelElementAbstract
{
    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-heading');
    }
}