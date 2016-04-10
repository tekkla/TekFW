<?php
namespace Core\Ajax\Commands\Act;

use Core\Ajax\AjaxCommandAbstract;

/**
 * PrintVar.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class PrintVar extends AjaxCommandAbstract
{

    protected $type = 'act';

    protected $fn = 'dump';

    /**
     * Creates a print_r console output of provided $var
     *
     * @param mixed $var
     */
    public function printVar($var)
    {
        $this->args = print_r($var, true);
    }
}
