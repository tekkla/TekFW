<?php
namespace Core\Lib\Html\Bootstrap\Panel;

use Core\Lib\Html\Elements\Div;

class PanelBody extends PanelElementAbstract
{

    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-body');
    }
}
