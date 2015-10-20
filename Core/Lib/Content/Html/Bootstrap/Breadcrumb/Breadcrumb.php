<?php
namespace Core\Lib\Content\Html\Bootstrap\Breadcrumb;

/**
 * Breadcrumb.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Breadcrumb
{

    private $breadcrumbs = [];

    /**
     * Adds a BreadcrumbObject to the breadcrumbs list
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Breadcrumb\Breadcrumb
     */
    public function addBreadcrumb(BreadcrumbObject $breadcrumb)
    {
        $this->breadcrumbs[] = $breadcrumb;

        return $this;
    }

    /**
     * Creates an active breadcrumb object and adds it to the crumbs list.
     *
     * @param string $text Text to show
     * @param string $title Title to use
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Breadcrumb\BreadcrumbObject
     */
    public function createActiveItem($text, $title = '')
    {
        $breadcrumb = new BreadcrumbObject();

        $breadcrumb->setText($text);
        $breadcrumb->setActive(true);

        if ($title) {
            $breadcrumb->setTitle($title);
        }

        $this->breadcrumbs[] = $breadcrumb;

        return $breadcrumb;
    }

    /**
     * Creates an breadcrumb object with link and adds it to the crumbs list.
     *
     * @param string $text Text to show
     * @param string $href Href of the link
     * @param string $title Title to use
     *
     * @return \Core\Lib\Content\Html\Bootstrap\Breadcrumb\BreadcrumbObject
     */
    public function createItem($text, $href, $title = '')
    {
        $breadcrumb = new BreadcrumbObject();

        $breadcrumb->setText($text);
        $breadcrumb->setHref($href);

        if ($title) {
            $breadcrumb->setTitle($title);
        }

        $this->breadcrumbs[] = $breadcrumb;

        return $breadcrumb;
    }

    /**
     * Returns all stored breadcrumbs
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }
}
