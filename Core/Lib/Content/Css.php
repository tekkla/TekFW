<?php
namespace Core\Lib\Content;

use Core\Lib\Cfg;

/**
 * Class for managing and creating of css objects
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
final class Css
{

	/**
	 * Storage of css objects
	 *
	 * @var array
	 */
	private static $css = [];

	/**
	 * Type of css object
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Css object content
	 *
	 * @var string
	 */
	private $content;

	/**
	 *
	 * @var Cfg
	 */
	private $cfg;

	/**
	 * Constructor
	 *
	 * @param Cfg $cfg
	 */
	public function __construct(Cfg $cfg)
	{
		$this->cfg = $cfg;
	}

	/**
	 * Initiates core css
	 */
	public function init()
	{
		// Add bootstrap main css file from cdn
		$this->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/css/bootstrap.min.css');

		// Add existing local user/theme related bootstrap file or load it from cdn
		if (file_exists($this->cfg->get('Core', 'dir_css') . '/bootstrap-theme.css')) {
			$this->link($this->cfg->get('Core', 'url_css') . '/bootstrap-theme.css');
		}
		else {
			$this->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/css/bootstrap-theme.min.css');
		}

		// Add existing font-awesome font icon css file or load it from cdn
		if (file_exists($this->cfg->get('Core', 'dir_css') . '/font-awesome-' . $this->cfg->get('Core', 'fontawesome_version') . '.min.css')) {
			$this->link($this->cfg->get('Core', 'url_css') . '/font-awesome-' . $this->cfg->get('Core', 'fontawesome_version') . '.min.css');
		}
		else {
			$this->link('https://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css');
		}

		// Add general TekFW css file
		$this->link($this->cfg->get('Core', 'url_css') . '/Core.css');
	}

	/**
	 * Adds a css object to the output queue
	 *
	 * @param Css $css
	 */
	public function &add()
	{
		self::$css[] = $this;
		return $this;
	}

	/**
	 * Compiles the objects in the output queue and adds them to SMFs $context.
	 * Optional: If set in framework config, multiple css files will be
	 * combined into one minified css file
	 */
	public function compile()
	{
		$files = [];
		$inline = [];

		$output = '';

		/* @var $css Css */
		foreach (self::$css as $css) {

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

		ob_start();

		foreach ($files as $file) {
			echo PHP_EOL . "\t" . '<link rel="stylesheet" type="text/css" href="', $file, '">';
		}

		if ($inline) {
			echo '<style>', implode(PHP_EOL, $inline), '</style>';
		}

		return ob_get_clean();
	}

	/**
	 * Sets objects type.
	 * Type can be "file" or "inline".
	 *
	 * @param string $type
	 *
	 * @throws Error
	 *
	 * @return \Core\Lib\Css
	 */
	public function setType($type)
	{
		$types = [
			'file',
			'inline'
		];

		if (! in_array($type, $types)) {
			Throw new \InvalidArgumentException('Css type must be "inline" or "file".');
		}

		$this->type = $type;

		return $this;
	}

	/**
	 * Sets objects css content.
	 *
	 * @param string $value
	 *
	 * @return \Core\Lib\Css
	 */
	public function setCss($value)
	{
		$this->content = $value;

		return $this;
	}

	/**
	 * Get objects type (file or inline)
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Get objects css content
	 *
	 * @return string
	 */
	public function getCss()
	{
		return $this->content;
	}

	/**
	 * Creates and returns a link css object.
	 *
	 * @param string $url
	 *
	 * @return Css
	 */
	public function &link($url)
	{
		$css = $this->di['core.content.css'];
		$css->setType('file');
		$css->setCss($url);

		return $css->add();
	}

	/**
	 * Creates and returns an inline css object
	 *
	 * @param string $styles
	 *
	 * @return \Core\Lib\Css
	 */
	public function &inline($styles)
	{
		$css = $this->di['core.content.css'];
		$css->setType('inline');
		$css->setCss($styles);

		return $css->add();
	}

	/**
	 * Returns the current stack off css commands
	 */
	public function getStack()
	{
		return self::$css;
	}
}
