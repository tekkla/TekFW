<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Div;

class PanelFooter extends PanelElementAbstract
{
    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-footer');
    }
}