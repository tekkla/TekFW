<?php
namespace Core\Lib\Traits;

/**
 *
 * @author Michael
 *
 */
trait DebugTrait
{

    public function ajaxDumpVar($var)
    {
        $this->di->get('core.ajax')->fnDumpVar($var);
    }

    public function ajaxPrintVar($var)
    {
        $this->di->get('core.ajax')->fnPrintVar($var);
    }

    public function fbLog($var)
    {
        \FB::log($var);
    }
}
