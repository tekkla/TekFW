<?php
namespace Core\Html\FormDesigner\Controls;

/**
 * ControleCollectionInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface ControlsCollectionInterface
{

    /**
     * Returns the stored controls collection
     */
    public function getControls();

    /**
     * Clears controls collection
     */
    public function clearControls();
}
