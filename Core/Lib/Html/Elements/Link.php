<?php
namespace Core\Lib\Html\Elements;

use Core\Lib\Html\HtmlAbstract;

/**
 * Link.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Link extends HtmlAbstract
{

    protected $element = 'link';

    public function setRel($rel)
    {
        $this->attribute['rel'] = $rel;

        return $this;
    }

    public function setType($type)
    {
        $this->attribute['type'] = $type;

        return $this;
    }

    public function setHref($href)
    {
        $this->attribute['href'] = $href;

        return $this;
    }
}
