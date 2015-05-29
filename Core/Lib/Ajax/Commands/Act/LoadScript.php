<?php
namespace Core\Lib\Ajax\Commands\Act;

use Core\Lib\Ajax\AjaxCommand;

/**
 * LoadScript.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class LoadScript extends AjaxCommand
{

    protected $type = 'act';

    protected $fn = 'load_script';

    /**
     * Creates ajax response to load a js file.
     *
     * @param string $file Complete url of file to load
     */
    public function loadScript($file)
    {
        $this->args = $file;
    }
}

