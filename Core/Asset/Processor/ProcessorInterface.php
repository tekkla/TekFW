<?php
namespace Core\Asset\Processor;

/**
 * ProcessorInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface ProcessorInterface
{

    /**
     * Processes
     *
     * @param unknown $content
     */
    public function process($content);
}
