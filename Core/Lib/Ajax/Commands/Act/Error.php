<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommandAbstract;

/**
 * Error.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Error extends AjaxCommandAbstract
{

    protected $type = 'act';

    protected $fn = 'error';

    /**
     * Send an error to the error div
     *
     * @param string $error Errormessage to show.
     * @param int $id Error id
     */
    public function error($error, $id = 0)
    {
        $this->args = [
            $error,
            $id
        ];
    }
}
