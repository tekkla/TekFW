<?php
namespace Core\Ajax\Commands\Act;

use Core\Ajax\AjaxCommandAbstract;

/**
 * Alert.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Alert extends AjaxCommandAbstract
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
