<?php
namespace Core\Lib\Traits;

/**
 * Analyze var trait
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2015 by author
 */
trait AnalyzeVarTrait
{

    public function fbLog($var)
    {
        \FB::log($var);
    }

    public function fbInfo($var)
    {
        \FB::info($var);
    }

    public function fbWarning($var)
    {
        \FB::warn($var);
    }

    public function fbDump($key, $var)
    {
        \FB::dump($key, $var);
    }

    public function var_dump($var, $exit = false)
    {
        if ($this->di->get('core.http.router')->isAjax()) {
            $this->di->get('core.ajax')->fnDump($var);
            return;
        }

        // Simple output to the browser
        if ($exit == true) {
            var_dump($var);
            exit();
        }

        // Adding to content output
        if (! property_exists($this, 'di')) {
            Throw new \RuntimeException('Analyzing var_dump needs access on the DI service container.');
        }

        ob_start();
        var_dump($var);
        $output = ob_get_clean();

        $this->di->get('core.content')->addDebug($output);
    }

    public function print_r($var, $exit = false)
    {
        if ($exit == true) {
            echo '<pre>', print_r($var, true), '</pre>';
            exit();
        }

        if (! property_exists($this, 'di')) {
            Throw new \RuntimeException('Analyzing print_r needs access on the DI service container.');
        }

        ob_start();
        echo '<pre>', print_r($var, true), '</pre>';
        $output = ob_get_clean();

        $this->di->get('core.content')->addDebug($output);
    }
}
