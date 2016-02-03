<?php
namespace Core\Lib\Html\Elements;

use Core\Lib\Errors\Exceptions\InvalidArgumentException;

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
     * @return \Core\Lib\Html\Elements\Abbr
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
     * @return \Core\Lib\Html\Elements\Abbr
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
            Throw new InvalidArgumentException('Set shape is not valid.', 1000);
        }

        $this->attribute['shape'] = $shape;

        return $this;
    }
}
