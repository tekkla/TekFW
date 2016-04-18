<?php
namespace Core\Html\Elements;

use Core\Html\HtmlException;

/**
 * Area.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Abbr extends Link
{

    protected $element = 'area';

    /**
     * Sets the coordinates of the area.
     *
     * @param string $cords
     * @return \Core\Html\Elements\Abbr
     *
     */
    public function setCoords($cords)
    {
        $this->attribute['coords'] = $cords;

        return $this;
    }

    /**
     * Sets the shape of the area.
     *
     * @param string $shape
     *
     * @throws InvalidArgumentException
     *
     * @return \Core\Html\Elements\Abbr
     */
    public function setShape($shape)
    {
        $shapes = array(
            'default',
            'rect',
            'circle',
            'poly'
        );

        if (! in_array($shape, $shapes)) {
            Throw new HtmlException('Set shape is not valid.', 1000);
        }

        $this->attribute['shape'] = $shape;

        return $this;
    }
}
