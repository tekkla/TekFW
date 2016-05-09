<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Div;

/**
 * PanelFooter.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class PanelFooter extends AbstractPanelElement
{

    public function __construct()
    {
        $this->html = new Div();
        $this->html->addCss('panel-footer');
    }
}
