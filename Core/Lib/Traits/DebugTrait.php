<?php
namespace Core\Lib\Traits;

use Core\Lib\Errors\Exceptions\RuntimeException;

/**
 * DebugTrait.php class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright  2015 by author
 * @license MIT
 */
trait DebugTrait
{
    /**
     * FirePHP log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    public function debugFbLog($var, $label=null)
    {
        \FB::log($var, $label);
    }

    /**
     * FirePHP Warn log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    public function debugFbWarn($var, $label=null)
    {
        \FB::warn($var, $label);
    }

    /**
     * FirePHP Info log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    public function debugFbInfo($var, $label=null)
    {
        \FB::info($var, $label);
    }

    /**
     * Sends FirePHP dump message
     *
     * @param mixed $var
     */
    public function debugFbDump($key, $var)
    {
        \FB::dump($key, $var);
    }

    /**
     * FirePHP Error log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    public function debugFbError($var, $label=null)
    {
        \FB::error($var, $label);
    }

    /**
     * More complex version of standard var_dunp() function.
     *
     * Result is either send to browser or as debug data to Content class.
     * On ajax request the result will be send to browser as ajax command.
     *
     * @param mixed $var
     * @param boolean $exit
     *
     * @throws RuntimeException
     */
    public function debugVarDump($var, $exit = false)
    {
        if (! property_exists($this, 'di')) {
            Throw new RuntimeException('DebugTrait::debugVarDump() method cannot work without access to DI service container. Make sure that the object using this trait has this property set.');
        }

        if ($this->di->get('core.http.router')->isAjax()) {
            $this->di->get('core.ajax')->fnDump($var);
            return;
        }

        // Simple output to the browser
        if ($exit == true) {
            var_dump($var);
            exit();
        }

        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        $this->di->get('core.content')->addDebug($output);
    }

    /**
     * More complex version of standard print_r() function.
     *
     * Result is either send to browser or as debug data to Content class.
     * On ajax request the result will be send to browser as ajax command.
     *
     * @param mixed $var
     * @param boolean $exit
     *
     * @throws RuntimeException
     */
    public function debugPrintR($var, $exit = false)
    {
        if (! property_exists($this, 'di')) {
            Throw new RuntimeException('DebugTrait::debugPrintR() method cannot work without access to DI service container. Make sure that the object using this trait has this property set.');
        }

        if ($this->di->get('core.http.router')->isAjax()) {
            $this->di->get('core.ajax')->fnPrint($var);
            return;
        }

        if ($exit == true) {
            echo '<pre>', print_r($var, true), '</pre>';
            exit();
        }

        if (! property_exists($this, 'di')) {
            Throw new RuntimeException('Analyzing print_r needs access on the DI service container.');
        }

        ob_start();
        echo '<pre>', print_r($var, true), '</pre>';
        $output = ob_get_clean();

        $this->di->get('core.content')->addDebug($output);
    }
}
