<?php
namespace Core\Lib\Traits;

/**
 * DebugTrait.php class
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright  2015 by author
 * @license MIT
 */
trait DebugTrait
{

    protected function ajaxDumpVar($var)
    {
        $this->di->get('core.ajax')->fnDumpVar($var);
    }

    protected function ajaxPrintVar($var)
    {
        $this->di->get('core.ajax')->fnPrintVar($var);
    }

    /**
     * FirePHP log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    protected function fbLog($var, $label=null)
    {
        \FB::log($var, $label);
    }

    /**
     * FirePHP Warn log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    protected function fbWarn($var, $label=null)
    {
        \FB::warn($var, $label);
    }

    /**
     * FirePHP Info log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    protected function fbInfo($var, $label=null)
    {
        \FB::info($var, $label);
    }

    /**
     * FirePHP Error log
     *
     * @param $var Var to log
     * @param string $label Optional label
     */
    protected function fbError($var, $label=null)
    {
        \FB::error($var, $label);
    }
}
