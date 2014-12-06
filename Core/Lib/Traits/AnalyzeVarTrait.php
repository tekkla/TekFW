<?php
namespace Core\Lib\Traits;

trait AnalyzeVarTrait
{

    public function var_dump($var, $exit = true)
    {
        var_dump($var);

        if ($exit == true) {
            exit();
        }
    }

    public function print_r($var, $return = false, $exit = true)
    {
       $content = print_r($var, $return);

        if ($exit == true) {
            exit();
        }

        return $content;
    }
}
