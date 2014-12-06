<?php
namespace Core\Lib\Content\Html\Controls;

use Core\Lib\Content\Html\Elements\Header;

class Navbar extends Header
{

	/**
	 * Flag for static navbar
	 * @var boolean
	 */
	private $static = false;

	/**
	 * Menuitems
	 * @var array
	 */
	private $items = [];

	/**
	 * Brand to show
	 * @var string
	 */
	private $brand = '';

	/**
	 * Url used to underlay brand
	 *
	 * @var string
	 */
	private $home_url = '/';

	/**
	 * Flag to wrap navbar by a BS container
	 *
	 * @var boolean
	 */
	private $container = true;

	/**
	 * Flag to create a multilevel menu
	 *
	 * @var boolean
	 */
	private $multilevel = false;

	protected $css = [
		'navbar',
		'navbar-default'
	];


	public function isStatic()
	{
		$this->static = true;
	}

	public function setStatic($static=true)
	{
		$this->static = true;
	}

	public function setItems(array $items)
	{
		$this->items = $items;
	}

	public function setBrand($brand, $home_url = '/')
	{
		$this->brand = $brand;
		$this->home_url = $home_url;
	}

	public function setMultilevel($multilevel=true)
	{
		$this->multilevel = $multilevel;
	}

	/**
	 * Sets container flag to wrap navbar into a BS container
	 *
	 * @param boolean $container
	 */
	public function setContainer($container=true) {
		$this->container = $container;
	}

	/**
	 * (non-PHPdoc)
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

					$html .='
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
