<?php
namespace Core\Data;

/**
 * SchemeHandlerInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface SchemeHandlerInterface
{

    /**
     *
     * @param array $scheme
     */
    public function setScheme(array $scheme);

    /**
     *
     * @param DataObjectInterface $data
     */
    public function excecute(DataObjectInterface $data);

    /**
     * Returns name of the set primary field in scheme.
     *
     * @return string|boolean Name of primary field or boolean false when no primary field is set
     */
    public function getPrimary();
}
