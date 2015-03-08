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

    private function ajaxDumpVar($var)
    {
        $this->di->get('core.ajax')->fnDumpVar($var);
    }

    private function ajaxPrintVar($var)
    {
        $this->di->get('core.ajax')->fnPrintVar($var);
    }

    private function fbLog($var)
    {
        \FB::log($var);
    }
}
