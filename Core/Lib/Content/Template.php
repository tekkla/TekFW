<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;
use Core\Lib\Content\Html\HtmlFactory;

class Template
{
	protected $head = '';

	protected $css;

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

	public function __construct(Cfg $cfg, Content $content, HtmlFactory $html)
	{
		$this->cfg = $cfg;
		$this->content = $content;
		$this->html =$html;
	}

	public function render()
	{
		if (!method_exists($this, 'Head')) {
			Throw new \RuntimeException('Head method missing');
		}

		if (!method_exists($this, 'Body')) {
			Throw new \RuntimeException('Body method missing');
		}

		$this->Head();
		$this->Body();
	}

	/**
	 * Creates and returns meta tags
	 *
	 * @return string
	 */
	public function getMeta()
	{
		$meta_stack = $this->content->meta->getTags();

		$html = '';

		foreach ($meta_stack as $tag) {

			$meta = $this->html->create('Elements\Meta');

			foreach ($tag as $attribute => $value) {
				$meta->addAttribute($attribute, $value);
			}

			$html .= PHP_EOL . $meta->build();
		}

		return $html;
	}

	/**
	 * Creates and returns the title tag
	 *
	 * @return string
	 */
	public function getTitle()
	{
		$title = $this->html->create('Elements\Title');
		$title->setInner($this->content->getTitle());

		return $title->build();
	}

	public function getNavbar()
	{
		$navbar = $this->html->create('Controls\Navbar');
		$navbar->setBrand($this->content->getBrand(), '/');
		$navbar->setItems($this->content->navbar->getMenu());

		return $navbar->build();
	}

	/**
	 * Creates and return OpenGraph tags
	 *
	 * @return string
	 */
	public function getOpenGraph()
	{
		$og_stack = $this->content->og->getTags();

		$html = '';

		foreach ($og_stack as $property => $content) {

			$meta = $this->html->create('Elements\Meta');
			$meta->addAttribute('property', $property);
			$meta->addAttribute('content', $content);

			$html .= PHP_EOL . $meta->build();
		}

		return $html;
	}

	/**
	 * Creates and returns all css realted content
	 *
	 *  @return string
	 */
	public function getCss()
	{
		// Get scripts of this area
		$css_stack = $this->content->css->getObjectStack();

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

			// Create link element
			$link = $this->html->create('Elements\Link');
			$link->setRel('stylesheet');
			$link->setType('text/css');
			$link->setHref($file);

			$html .= PHP_EOL . $link->build();
		}

		if ($inline) {

			$style = $this->html->create('Elements\Style');
			$style->setInner(implode(PHP_EOL, $inline));

			$html .= PHP_EOL . $style->build;
		}

		return $html;
	}

	/**
	 * Creates and returns js script stuff for the requested area.
	 *
	 * @param string $area Valid areas are 'top' and 'below'.
	 *
	 * @return string
	 */
	public function getScript($area)
	{
		// Get scripts of this area
		$script_stack = $this->content->js->getScriptObjects($area);

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
			$html .= PHP_EOL . '<!-- ' . strtoupper($area). ' JAVASCRIPTS -->';
		}

		if ($vars || $scripts || $ready) {

			// Create script html object
			$script = $this->html->create('Elements\Script');

			foreach ($vars as $name => $val) {
				$script->addInner(PHP_EOL . 'var ' . $name . ' = ' . (is_string($val) ? '"' . $val . '"' : $val) . ';');
			}

			// Create $(document).ready()
			if ($ready) {
				$script->addInner('$(document).ready(function() {' . PHP_EOL);
				$script->addInner(implode(PHP_EOL, $ready) . PHP_EOL);
				$script->addInner('});');

				if ($this->cfg->get('Core', 'js_minify')) {
					$script->setInner(\JSMin::minify($script->getInner()));
				}
			}

			$html .= PHP_EOL . $script->build();
		}

		// Add complete blocks
		$html .= implode(PHP_EOL, $blocks);

		// Create files
		foreach ($files as $file) {

			// Create script html object
			$script = $this->html->create('Elements\Script');
			$script->setSrc($file);

			$html .= PHP_EOL . $script->build();
		}

		return $html;
	}

	/**
	 * Create and returns head link elements
	 *
	 * @return string
	 */
	public function getHeadLinks()
	{
		$link_stack = $this->content->link->getLinkStack();

		$html = '';

		foreach ($link_stack as $link){

			$link = $this->html->create('Elements\Link');

			foreach ($link as $attribute => $value) {
				$link->addAttribute($attribute, $value);
			}

			$html .= $link->build();
		}

		return $html;
	}


	/**
	 * Creates and returns stored messages
	 *
	 * @return string
	 */
	public function getMessages($data_only=false)
	{
		$messages = $this->content->msg->getMessages();

		if ($data_only) {
			return $messages;
		}

		$html ='';

		foreach ($messages as $msg) {

			$html .= PHP_EOL .'
		<div class="alert alert-' . $msg->getType() . ' alert-dismissable' . ($msg->getFadeout() ? ' fadeout' : '') . '">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			' . $msg->getMessage() . '
		</div>';

			/*
			$div->addCss([
				'alert',
				'alert-' . $msg->getType(),
				'alert-dismissable',
			]);

			if ($msg->getFadeout()) {
				$div->addCss('fadeout');
			}

			$div->setInner('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $msg->getMessage());

			$html .= PHP_EOL . $div->build();

			*/
		}

		return $html;

	}

	public function getBreadcrumbs($data_only=false)
	{
		$breadcrumbs = $this->content->breadcrumbs->getBreadcrumbs();

		if ($data_only) {
			return $breadcrumbs;
		}

		$html = '';

		if ($breadcrumbs) {

			$html .= '<ol class="breadcrumb">';

			foreach ($breadcrumbs as $breadcrumb) {

				$html .= '<li';

				if ($breadcrumb->getActive()) {
					$html .= ' class="active">' . $breadcrumb->getText();
				}
				else {
					$html .= '><a href="' . $breadcrumb->getHref() . '">' . $breadcrumb->getText() . '</a>';
				}

				$html .= '</li>';
			}


			$html .= '</ol>';
		}

		return $html;
	}

	public function getContent()
	{
		return $this->content->getContent();
	}
}
