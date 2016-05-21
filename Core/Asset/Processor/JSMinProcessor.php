<?php
namespace Core\Asset\Processor;

/**
 * JSMinProcessor.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class JSMinProcessor implements ProcessorInterface
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\ProcessorInterface::process()
     *
     */
    public function process($content)
    {
        return \JSMin::minify($content);
    }
}
