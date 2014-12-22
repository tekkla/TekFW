<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Elements\Header;

class Navbar extends Header
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
     * Flag to wrap navbar by a BS container
     *
     * @var boolean
     */
    protected $container = true;

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

    public function isStatic()
    {
        $this->static = true;

        return $this;
    }

    public function setStatic($static = true)
    {
        $this->static = true;

        return $this;
    }

    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    public function setBrand($brand, $home_url = '/')
    {
        $this->brand = $brand;
        $this->home_url = $home_url;

        return $this;
    }

    public function setMultilevel($multilevel = true)
    {
        $this->multilevel = $multilevel;

        return $this;
    }

    /**
     * Sets container flag to wrap navbar into a BS container
     *
     * @param boolean $container
     */
    public function setContainer($container = true)
    {
        $this->container = $container;

        return $this;
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
