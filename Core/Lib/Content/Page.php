<?php
namespace Core\Lib\Content;

use Core\Lib\Request;
use Core\Lib\Cfg;
use Core\Lib\Amvc\Creator;
use Core\Lib\Content\LinktreeElement;
use Core\Lib\Content\Message;

/**
 * Page delivery class
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @copyright 2014
 * @license MIT
 * @package TekFW
 * @subpackage Lib
 */
class Page
{
	private $linktree = [];

	private $meta = [];

	private $title = '';

	private $description = '';

	private $request;
	private $cfg;
	private $js;
	private $css;
	private $msg;
	private $appcreator;


	public function __construct(
		Request $request,
		Cfg $cfg,
		Javascript $js,
		Css $css,
		Message $msg,
		Creator $appcreator
	)
	{
		$this->request = $request;
		$this->cfg = $cfg;
		$this->js = $js;
		$this->css = $css;
		$this->msg = $msg;
		$this->appcreator = $appcreator;
	}

	/**
	 * Inits possible set content handler and adds copyright infos about the framework to context.
	 * Page handler is the name of an app. If set, an instance of this app is created and looked
	 * for an initContentHandler method to be run.
	 */
	public function init()
	{
		// try to init possible content handler
		if ($this->cfg->exists('Core', 'content_handler') && $this->request->isAjax())
		{
			// Get instance of content handler app
			$app = $this->appcreator->create($this->cfg->get('Core', 'content_handler'));

			// Init method to call exists?
			if (method_exists($app, 'initContentHandler')) {
				$app->initContentHandler();
			}
		}
	}

	public function addLinktree(LinktreeElement $linktree_element)
	{
		$this->linktree[] = $linktree_element;
	}

	public function setTitle($title)
	{
		$this->title = $title;
	}

	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Builds the output and echoes it to the world.
	 * Before echoing the content, a set cotent handler is called and - if set in cfg - all URLs
	 * will converted into SEO friendly URLs
	 * @see Url
	 * @throws Error
	 */
	public function build($content)
	{
		// Combine cached above and below with content
		echo '<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>' . $this->title . '</title>';

	echo $this->css->compile();
	echo $this->js->compile(false);

	echo'
</head>

<body>
	<div class="container">
		<div id="message">';

		$messages = $this->msg->getMessages();

		if ($messages)
		{
			foreach ( $messages as $msg ) {
				echo PHP_EOL . $msg->build();
			}
		}

		echo '</div>';

		// Fill in content
		try
		{
			// Try to run set content handler on non ajax request
			if ($this->cfg->exists('Core', 'content_handler') && ! $this->request->isAjax())
			{
				// We need the name of the ContentCover app
				$app_name = $this->cfg->get('Core', 'content_handler');

				// Get instance of this app
				$app = $this->appcreator->create($app_name);

				// Check for existing ContenCover method
				if (!method_exists($app, 'runContentHandler')) {
					Throw new \RuntimeException('You set the app "' . $app_name . '" as content handler but it lacks of method "runContentHandler()". Correct either the config or add the needed method to this app.');
				}

				// Everything is all right. Run content handler by giving the current content to it.
				echo $app->runContentHandler($content);
			}
		}
		catch ( \Exception $e )
		{
			// Add error message above content
			echo '<div class="alert alert-danger alert-dismissable">' . $e->getMessage() . '</div>';
		}

		## Insert content
		echo $content;

		## Create below content

		// These divs are used for info displays and page control
		echo '
		<div id="status"><i class="fa fa-spinner fa-spin"></i></div>
		<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"></div>
		<div id="debug"></div>
		<div id="tooltip"></div>
		<div id="scrolltotop"></div>';

	if ($this->cfg->get('Core', 'log') && $this->cfg->get('Core', 'log_handler') == 'page')
	{
		echo $this->log->get();
		$this->log->reset();

	}

	echo '
	</div>';

	echo $this->js->compile(true);

	echo '
</body>

</html>';

		// Experimental SEO url converter...
		#if (Cfg::get('Core', 'url_seo'))
		#{
		#	$match_it = function($match) {
		#		return Url::convertSEF($match);
		#	};

		#	$html = preg_replace_callback('@(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()]+|\(([^\s()]+|(\([^\s()]+\)))*\))+(?:\(([^\s()]+|(\([^\s()]+\)))*\)|[^\s`!()\[\]{};:\'".,?«»“”‘’]))@', $match_it($match), $html);
		#}
		#echo $html;
	}

	/**
	 * Checks for a set config handler in web config
	 * @return boolean
	 */
	public function hasContentHandler()
	{
		return $this->cfg->exists('Core', 'content_handler');
	}

	/**
	 * Returns the name of config handler set in web config
	 * @return string
	 */
	public function getContenHandler()
	{
		return $this->cfg->get('Core', 'content_handler');
	}
}
