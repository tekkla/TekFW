<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommandAbstract;

/**
 * Console.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Console extends AjaxCommandAbstract
{

    protected $type = 'act';

    protected $fn = 'console';

    /**
     * Create console log output
     *
     * @param string $msg
     */
    public function console($msg)
    {
        $this->args = $msg;
    }
}

