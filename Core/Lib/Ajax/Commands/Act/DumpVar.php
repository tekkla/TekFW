<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommand;

/**
 * DumpVar.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class DumpVar extends AjaxCommand
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
