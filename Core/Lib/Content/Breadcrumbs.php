<?php
namespace Core\Lib\Content;

/**
 *
 * @author Michael
 *
 */
class Breadcrumbs
{
	private $breadcrumbs = [];

	public function addBreadcrumb(BreadcrumbsObject $breadcrumb)
	{
		$this->breadcrumbs[] = $breadcrumb;
	}

	public function &createActiveItem($text, $title='')
	{
		$breadcrumb = new BreadcrumbsObject();
		$breadcrumb->setText($text);
		$breadcrumb->setActive(true);

		if ($title) {
			$breadcrumb->setTitle($title);
		}

		$this->breadcrumbs[] = &$breadcrumb;

		return $breadcrumb;
	}

	public function &createItem($text, $href, $title='')
	{
		$breadcrumb = new BreadcrumbsObject();
		$breadcrumb->setText($text);
		$breadcrumb->setHref($href);

		if ($title) {
			$breadcrumb->setTitle($title);
		}

		$this->breadcrumbs[] = &$breadcrumb;

		return $breadcrumb;
	}

	public function getBreadcrumbs()
	{
		return $this->breadcrumbs;
	}
}
