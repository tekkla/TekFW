<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommandAbstract;

/**
 * DumpVar.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DumpVar extends AjaxCommandAbstract
{

    protected $type = 'act';

    protected $fn = 'dump';

    /**
     * Creates a var_dump console output of provided $var
     *
     * @param mixed $var
     */
    public function dumpVar($var)
    {
        ob_start();

        var_dump($var);

        $this->args = ob_get_clean();
    }
}
