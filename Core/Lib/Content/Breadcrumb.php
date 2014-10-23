<?php
namespace Core\Lib\Content;

/**
 *
 * @author Michael
 *
 */
class Breadcrumb
{
	private $breadcrumbs = [];

	/**
	 * Adds a BreadcrumbObject to the breadcrumbs list
	 *
	 * @return \Core\Lib\Content\Breadcrumbs
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
	 * @return \Core\Lib\Content\Breadcrumbs
	 */
	public function createActiveItem($text, $title='')
	{
		$breadcrumb = new BreadcrumbObject();
		$breadcrumb->setText($text);
		$breadcrumb->setActive(true);

		if ($title) {
			$breadcrumb->setTitle($title);
		}

		$this->breadcrumbs[] = $breadcrumb;

		return $this;
	}

	/**
	 * Creates an breadcrumb object with link and adds it to the crumbs list.
	 *
	 * @param string $text
	 * @param string $href
	 * @param string $title
	 *
	 * @return \Core\Lib\Content\Breadcrumbs
	 */
	public function createItem($text, $href, $title='')
	{
		$breadcrumb = new BreadcrumbObject();
		$breadcrumb->setText($text);
		$breadcrumb->setHref($href);

		if ($title) {
			$breadcrumb->setTitle($title);
		}

		$this->breadcrumbs[] = $breadcrumb;

		return $this;
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
