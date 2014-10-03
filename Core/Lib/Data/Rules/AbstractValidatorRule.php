<?php
namespace Lib\Data;

use Core\Lib\Amvc\Model;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 *        
 */
abstract class AbstractValidatorRule
{

    /**
     *
     * @var Model
     */
    private $model;

    /**
     */
    function __construct(Model $model)
    {
        $this->model = $model;
    }

    abstract function validate();
}

?>