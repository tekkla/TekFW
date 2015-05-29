<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommand;

/**
 * Refresh.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Refresh extends AjaxCommand
{

    protected $type = 'act';

    protected $fn = 'refresh';

    /**
     * Calls a page refresh by loading the provided url.
     * Calls location.href="url" in page.
     *
     * @param string $url Url to redirect
     */
    public function refresh($url)
    {
        $this->args = $url;
    }
}
