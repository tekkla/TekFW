<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Div;

class PanelBody extends PanelElementAbstract
{

    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-body');
    }
}
