<?php
namespace Core\Asset\Processor;

/**
 * ReplaceProcessor.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class ReplaceProcessor implements ProcessorInterface
{

    /**
     *
     * @var string
     */
    private $search = '';

    /**
     *
     * @var string
     */
    private $replace = '';

    /**
     * Constructor
     *
     * @param string $search
     * @param string $replace
     */
    public function __construct($search, $replace)
    {
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Asset\Processor\ProcessorInterface::process()
     */
    public function process($content)
    {
        return str_replace($this->search, $this->replace, $content);
    }
}
