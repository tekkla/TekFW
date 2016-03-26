<?php
namespace Core\Lib\Html\Bootstrap\Panel;

use Core\Lib\Html\Elements\Div;

class PanelFooter extends PanelElementAbstract
{
    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-footer');
    }
}
