<?php
namespace Core\Lib\Traits;

/**
 * Url Trait
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @license MIT
 * @copyright 2014
 */
trait UrlTrait
{

    /**
     * Generates url by using routename and optional prameters
     *
     * @param string $route
     *            Name of route to compile
     * @param array $params
     *            Optional parameter list
     */
    protected function url($route, Array $params = [])
    {
        return $this->di->get('core.http.router')->url($route, $params);
    }
}
