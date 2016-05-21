<?php
namespace Core\Asset\Processor;

use JShrink\Minifier;

/**
 * JShrinkProcessor.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class JShrinkProcessor implements ProcessorInterface
{

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Asset\Processor\ProcessorInterface::process()
     */
    public function process($content)
    {
        return Minifier::minify($content);
    }
}
