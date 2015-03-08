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

    protected function fbLog($var)
    {
        \FB::log($var);
    }
}
