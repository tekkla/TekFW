<?php
namespace Core\Asset\Processor;

/**
 * CssMinProcessor.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class CssMinProcessor implements ProcessorInterface
{

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\ProcessorInterface::process()
     *
     */
    public function process($content)
    {
        $min = new \CSSmin();
        return $min->run($content);
    }
}
