<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Content\Html\HtmlFactory;

class Template
{

	/**
	 * Layers to render
	 *
	 * @var array
	 */
	protected $layers = [
		'Head',
		'Body'
	];


	/**
	 *
	 * @var Cfg
	 */
	private $cfg;

	/**
	 *
	 * @var Content
	 */
	private $content;

	/**
	 *
	 * @var HtmlFactory
	 */
	private $html;

	/**
	 * Constructor
	 *
	 * @param Cfg $cfg
	 * @param Content $content
	 * @param HtmlFactory $html
	 */
	public function __construct(Cfg $cfg, Content $content, HtmlFactory $html)
	{
		$this->cfg = $cfg;
		$this->content = $content;
		$this->html = $html;
	}

	/**
	 * Renders the template
	 *
	 * Uses the $layer property to look for layers to be rendered. Will throw a
	 * runtime exception when a requested layer does not exist in the called
	 * template file.
	 *
	 * @throws \RuntimeException
	 */
	final public function render()
	{
		foreach ($this->layers as $layer) {
			if (!method_exists($this, $layer)) {
				Throw new \RuntimeException('Template Error: The requested layer "' . $layer . '" does not exist.');
			}

			$this->$layer();
		}
	}

	/**
	 * Creates and returns meta tags
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return string
	 */
	final protected function getMeta($data_only = false)
	{
		$meta_stack = $this->content->meta->getTags();

		if ($data_only) {
			return $meta_stack;
		}

		$html = '';

		foreach ($meta_stack as $tag) {

			$meta = $this->html->create('Elements\Meta');

			$html .= PHP_EOL . '<meta';

			foreach ($tag as $attribute => $value) {
				$html .= ' ' . $attribute . '="' . $value. '"';
			}

			$html .= '>';
		}

		return $html;
	}

	/**
	 * Creates and returns the title tag
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return string
	 */
	final protected function getTitle($data_only = false)
	{
		if ($data_only) {
			return $this->content->getTitle();
		}

		return PHP_EOL . '<title>' . $this->content->getTitle() . '</title>';
	}

	/**
	 * Returns html navbar or only its data.
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return string|array
	 */
	final protected function getNavbar($data_only = false)
	{
		if ($data_only) {
			return [
				'brand' => $this->content->getBrand(),
				'items' => $this->content->navbar->getMenu()
			];
		}

		$navbar = $this->html->create('Controls\Navbar');
		$navbar->setBrand($this->content->getBrand(), '/');
		$navbar->setItems($this->content->navbar->getMenu());

		return $navbar->build();
	}

	/**
	 * Creates and return OpenGraph tags
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return string
	 */
	final protected function getOpenGraph($data_only = false)
	{
		$og_stack = $this->content->og->getTags();

		if ($data_only) {
			return $og_stack;
		}

		$html = '';

		foreach ($og_stack as $property => $content) {
			$html .= '<meta property="' . $property . '" content="' . $content .'">' . PHP_EOL;
		}

		return $html;
	}

	/**
	 * Creates and returns all css realted content
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return array|string
	 */
	final protected function getCss($data_only = false)
	{
		$css_stack = $this->content->css->getObjectStack();

		if ($data_only) {
			return $css_stack;
		}

		$files = [];
		$inline = [];

		$html = '';

		/* @var $css Css */
		foreach ($css_stack as $css) {

			switch ($css->getType()) {
				case 'file':
					$files[] = $css->getCss();
					break;

				case 'inline':
					$inline[] = $css->getCss();
					break;
			}
		}

		// create script for minifier
		if ($this->cfg->get('Core', 'css_minify')) {

			foreach ($files as $file) {

				if (strpos($file['filename'], BASEURL) !== false) {

					$board_parts = parse_url(BASEURL);
					$url_parts = parse_url($file['filename']);

					// Do not try to minify ressorces from external host
					if ($board_parts['host'] != $url_parts['host'])
						continue;

						// Store filename in minify list
					$files_to_min[] = '/' . $url_parts['path'];
				}
			}

			if ($files_to_min) {
				$_SESSION['min_css'] = $files_to_min;
				$files = (array) $this->cfg->get('Core', 'url_tools') . '/min/g=css';
			}
		}

		foreach ($files as $file) {
			$html .= PHP_EOL .  '<link rel="stylesheet" type="text/css" href="' . $file . '">';
		}

		if ($inline) {
			$html .= PHP_EOL . '<style>' . PHP_EOL . implode(PHP_EOL, $inline) . PHP_EOL . '</style>' . PHP_EOL;
		}

		return $html;
	}

	/**
	 * Creates and returns js script stuff for the requested area.
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param string $area Valid areas are 'top' and 'below'.
	 * @param boolean $data_only
	 *
	 * @return array|string
	 */
	final protected function getScript($area, $data_only = false)
	{
		// Get scripts of this area
		$script_stack = $this->content->js->getScriptObjects($area);

		if ($data_only) {
			return $script_stack;
		}

		// Init js storages
		$files = $blocks = $inline = $scripts = $ready = $vars = [];

		// Include JSMin lib
		if ($this->cfg->get('Core', 'js_minify')) {
			require_once ($this->cfg->get('Core', 'dir_tools') . '/min/lib/JSMin.php');
		}

		/* @var $script Javascript */
		foreach ($script_stack as $key => $script) {

			switch ($script->getType()) {

				// File to lin
				case 'file':
					$files[] = $script->getScript();
					break;

				// Script to create
				case 'script':
					$inline[] = $this->cfg->get('Core', 'js_minify') ? \JSMin::minify($script->getScript()) : $script->getScript();
					break;

				// Dedicated block to embaed
				case 'block':
					$blocks[] = PHP_EOL . $script->getScript();
					break;

				// A variable to publish to global space
				case 'var':
					$var = $script->getScript();
					$vars[$var[0]] = $var[1];
					break;

				// Script to add to $.ready()
				case 'ready':
					$ready[] = $this->cfg->get('Core', 'js_minify') ? \JSMin::minify($script->getScript()) : $script->getScript();
					break;
			}

			// Remove worked script object
			unset($script_stack[$key]);
		}

		// Are there files to minify?
		if ($this->cfg->get('Core', 'js_minify')) {

			if ($files) {
				$to_minfiy = [];
			}

			foreach ($files as $file) {

				// Process only files that come from the sitecontext
				if (strpos($file['filename'], BASEURL) !== false) {

					// Compare host to get sure
					$board_parts = parse_url(BASEURL);
					$url_parts = parse_url($file['filename']);

					if ($board_parts['host'] != $url_parts['host']) {
						continue;
					}

					// Store filename in minify list
					if (! in_array('/' . $url_parts['path'], $files)) {
						$to_minfiy[] = '/' . $url_parts['path'];
					}
				}
			}

			// Are there files to combine?
			if ($to_minfiy) {

				// Store files to minify in session
				$_SESSION['min']['js-' . $area] = $to_minfiy;

				// Add link to combined js file
				$files = [
					$this->cfg->get('Core', 'url_tools') . '/min/g=js-' . $area
				];
			}
		}

		// Init output var
		$html = '';

		// Create compiled output
		if ($vars || $scripts || $ready || $files) {
			$html .= PHP_EOL . '<!-- ' . strtoupper($area) . ' JAVASCRIPTS -->';
		}

		if ($vars || $scripts || $ready) {

			// Create script html object
			$script = '<script>';

			foreach ($vars as $name => $val) {
				$script .= (PHP_EOL . 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';');
			}

			// Create $(document).ready()
			if ($ready) {
				$script .= PHP_EOL . '$(document).ready(function() {' . PHP_EOL;
				$script .= implode(PHP_EOL, $ready);
				$script .= PHP_EOL . '});';
			}

			$script .= PHP_EOL . '</script>';

			// Minify script?
			if ($this->cfg->get('Core', 'js_minify')) {
				$script = \JSMin::minify($script);
			}

			$html .= PHP_EOL . $script;
		}

		// Add complete blocks
		$html .= implode(PHP_EOL, $blocks);

		// Create files
		foreach ($files as $file) {

			// Create script html object
			$html .= PHP_EOL . '<script src="' . $file .'"></script>';
		}

		return $html;
	}

	/**
	 * Create and returns head link elements
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return array|string
	 */
	final protected function getHeadLinks($data_only = false)
	{
		$link_stack = $this->content->link->getLinkStack();

		if ($data_only) {
			return $link_stack;
		}

		$html = '';

		foreach ($link_stack as $link) {

			$html .= PHP_EOL . '<link';

			foreach ($link as $attribute => $value) {
				$html .= ' ' . $attribute . '="' . $value . '"';
			}

			$html .= '>';
		}

		return $html;
	}

	/**
	 * Creates and returns stored messages
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return array|string
	 */
	final protected function getMessages($data_only = false)
	{
		$messages = $this->content->msg->getMessages();

		if ($data_only) {
			return $messages;
		}

		$html = '';

		foreach ($messages as $msg) {

			$html .= PHP_EOL . '
			<div class="alert alert-' . $msg->getType() . ' alert-dismissable' . ($msg->getFadeout() ? ' fadeout' : '') . '">
				<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
				' . $msg->getMessage() . '
			</div>';
		}

		return $html;
	}

	/**
	 * Creates breadcrumb html control or returns it's data
	 *
	 * Set $data_only argument to true if you want to get get only the data
	 * without a genereated html control.
	 *
	 * @param boolean $data_only
	 *
	 * @return array|string
	 */
	final protected function getBreadcrumbs($data_only = false)
	{
		$breadcrumbs = $this->content->breadcrumbs->getBreadcrumbs();

		if ($data_only) {
			return $breadcrumbs;
		}

		// Add home button
		$text = $this->content->txt('home');

		if ($breadcrumbs) {
			$home_crumb = $this->content->breadcrumbs->createItem($text, BASEURL, $text);
		} else {
			$home_crumb = $this->content->breadcrumbs->createActiveItem($text, $text);
		}

		array_unshift($breadcrumbs, $home_crumb);


		$html = '';

		if ($breadcrumbs) {

			$html .= '<ol class="breadcrumb">';

			foreach ($breadcrumbs as $breadcrumb) {

				$html .= '<li';

				if ($breadcrumb->getActive()) {
					$html .= ' class="active">' . $breadcrumb->getText();
				} else {
					$html .= '><a href="' . $breadcrumb->getHref() . '">' . $breadcrumb->getText() . '</a>';
				}

				$html .= '</li>';
			}

			$html .= '</ol>';
		}

		return $html;
	}

	/**
	 * Returns the content generated by app call
	 */
	final protected function getContent()
	{
		return $this->content->getContent();
	}
}
