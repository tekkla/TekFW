<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommand;

/**
 *
 * @author Michael
 *
 */
class Alert extends AjaxCommand
{

    protected $type = 'act';

    protected $fn = 'alert';

    /**
     * Create an alert in browser
     *
     * @param $alert
     */
    public function alert($alert)
    {
        $this->args = $alert;
    }
}
