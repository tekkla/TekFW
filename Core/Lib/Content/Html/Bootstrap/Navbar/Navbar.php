<?php
namespace Core\Lib\Content\Html\Bootstrap\Navbar;

use Core\Lib\Content\Html\Elements\Nav;
use Core\AppsSec\Core\Exception\HtmlException;

class Navbar extends Nav
{

    /**
     * Flag for static navbar
     *
     * @var boolean
     */
    protected $static = false;

    /**
     * Menuitems
     *
     * @var array
     */
    private $items = [];

    /**
     * Brand to show
     *
     * @var string
     */
    protected $brand = '';

    /**
     * Url used to underlay brand
     *
     * @var string
     */
    protected $home_url = '/';

    /**
     * Flag to wrap navbar by a BS fluid container
     *
     * @var boolean
     */
    protected $fluid = false;

    /**
     * Flag to create a multilevel menu
     *
     * @var boolean
     */
    protected $multilevel = false;

    protected $css = [
        'navbar',
        'navbar-default'
    ];

    private $element_types = [
        'brand',
        'link',
        'dropdown',
        'form',
        'text'
    ];

    public function isStatic($static = null)
    {
        if (isset($static)) {
            $this->static = (bool) $static;
            return $this;
        }
        else {
            return $this->static;
        }
    }

    public function isFluid($fluid = null)
    {
        if (isset($fluid)) {
            $this->fluid = (bool) $fluid;
            return $this;
        }
        else {
            return $this->fluid;
        }
    }

    public function isCollapsible($collapsible = null)
    {
        if (isset($collapsible)) {
            $this->collapsible = (bool) $collapsible;
            return $this;
        }
        else {
            return $this->collapsible;
        }
    }


    public function addNavbarElement(NavbarElementAbstract $element)
    {
        if (! in_array($element->getType(), $this->element_types)) {
            Throw new HtmlException(sprintf('They type "%s" is not a valid navbar elementtype. Allowed are %s.', $element->getType(), implode(', ', $this->element_types)));
        }

        $this->elements[] = $element;
    }

    public function &createNavbarElement($type)
    {
        if (! in_array($type, $this->element_types)) {
            Throw new HtmlException(sprintf('They type "%s" is not a valid navbar elementtype. Allowed are %s.', $type, implode(', ', $this->element_types)));
        }

        return $this->elements[] = $this->factory->create('Bootstrap\Navbar\\' . $type . 'Element');
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Core\Lib\Abstracts\HtmlAbstract::build()
     */
    public function build()
    {
        if ($this->static) {
            $this->css[] = 'navbar-static-top';
        }

        if ($this->container) {
            $this->inner .= '<div class="container">';
        }

        $this->inner .= '
		<div class="navbar-header">
			<button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".main-menu-collapse">
				<span class="sr-onlyToggle navigation"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>';

        if ($this->brand) {
            $this->inner .= '<a href="' . $this->home_url . '" class="navbar-brand">' . $this->brand . '</a>';
        }

        $this->inner .= '
		</div>
		<nav class="collapse navbar-collapse main-menu-collapse" role="navigation">
			<ul class="nav navbar-nav">';

        $this->inner .= $this->buildMenuItems($this->items);

        $this->inner .= '
			</ul>
		</nav>';

        if ($this->container) {
            $this->inner .= '</div>';
        }

        return parent::build();
    }

    /**
     * Builds nav bar elements
     *
     * @param array $items
     *
     * @return string
     */
    private function buildMenuItems(array $items)
    {
        $html = '';

        foreach ($items as $item) {

            if ($this->multilevel && $item->hasChilds()) {
                $html .= '
				<li class="navbar-parent">
					<a href="' . $item->getUrl() . '">' . $item->getText() . '</a>
					<ul>';

                $html .= $this->buildMenuItems($item->getChilds());

                $html .= '
					</ul>
				</li>';
            }
            else {

                $url = $item->getUrl();

                if ($url) {
                    $html .= '<li><a href="' . $url . '">' . $item->getText() . '</a></li>';
                }
                else {
                    $html .= '<li><a href="#" onClick="return false">' . $item->getText() . '</a></li>';
                }
            }
        }

        return $html;
    }
}
