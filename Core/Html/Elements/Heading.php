<?php
namespace Core\Html\Elements;

use Core\Html\HtmlAbstract;
use Core\Html\HtmlException;

/**
 * Heading.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Heading extends HtmlAbstract
{

    /**
     * Size of heading.
     *
     * @var int
     */
    private $size = 1;

    /**
     * Element to build
     *
     * @var string
     */
    protected $element = 'h1';

    /**
     * Set Heading element size from 1-6.
     *
     * @param integer
     *
     * @throws InvalidArgumentException
     *
     * @return Heading
     */
    public function setSize($size)
    {
        $sizes = [
            1,
            2,
            3,
            4,
            5,
            6
        ];

        if (! in_array((int) $size, $sizes)) {
            Throw new HtmlException('Size "' . $size . '" is not an allowed size for heading html elements');
        }

        $this->element = 'h' . $size;

        return $this;
    }
}
