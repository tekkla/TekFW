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
	 * Storage of core css objects
	 *
	 * @var array
	 */
	private static $core_css = [];

	/**
	 * Storage of app css objects
	 *
	 * @var array
	 */
	private static $app_css = [];

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

	private $mode = 'apps';

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
		$this->mode = 'core';

		$bootstrap_css = $this->cfg->get('Core', 'theme') . '/bootstrap.min.css';

		// Add existing local user/theme related bootstrap file or load it from cdn
		if (file_exists(THEMESDIR . '/' . $bootstrap_css)) {
			$this->link(THEMESURL .  '/' . $bootstrap_css);
		}
		else {

			// Add bootstrap main css file from cdn
			$this->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/css/bootstrap.min.css');

			// Add existing local user/theme related bootstrap file or load it from cdn
			if (file_exists($this->cfg->get('Core', 'dir_css') . '/bootstrap-theme.css')) {
				$this->link($this->cfg->get('Core', 'url_css') . '/bootstrap-theme.css');
			}
			else {
				$this->link('https://maxcdn.bootstrapcdn.com/bootstrap/' . $this->cfg->get('Core', 'bootstrap_version') . '/css/bootstrap-theme.min.css');
			}
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

		$this->mode = 'apps';
	}

	/**
	 * Adds a css object to the output queue
	 *
	 * @param Css $css
	 */
	public function &add(CssObject $css)
	{
		if ($this->mode == 'core') {
			self::$core_css[] = $css;
		} else {
			self::$app_css[] = $css;
		}

		return $css;
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
		$css = new CssObject();
		$css->setType('file');
		$css->setCss($url);

		return $this->add($css);
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
		$css = new CssObject();
		$css->setType('inline');
		$css->setCss($styles);

		return $this->add($css);
	}

	/**
	 * Returns the current stack off css commands
	 */
	public function getObjectStack()
	{
		return array_merge(self::$core_css, self::$app_css);
	}
}
