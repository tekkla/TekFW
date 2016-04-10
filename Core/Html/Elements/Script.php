<?php
namespace Core\Html\Elements;

use Core\Html\HtmlAbstract;

/**
 * Script.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Script extends HtmlAbstract
{

    protected $element = 'script';

    public function setType($type)
    {
        $this->attribute['type'] = $type;

        return $this;
    }

    public function setSrc($src)
    {
        $this->attribute['src'] = $src;

        return $this;
    }

    public function setCharset($charset)
    {
        $this->attribute['charset'] = $charset;

        return $this;
    }

    public function setFor($for)
    {
        $this->attribute['for'] = $for;

        return $this;
    }

    public function setDefer()
    {
        $this->addAttribute('defer');

        return $this;
    }

    public function setAsync()
    {
        $this->addAttribute('async');

        return $this;
    }
}
