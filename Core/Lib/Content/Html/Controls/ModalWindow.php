<?php
namespace Core\Lib\Content\Html\Controls;

/**
 * Creates a Bootstrap modal window control
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Controls
 * @license MIT
 * @copyright 2014 by author
 */
class ModalWindow
{
	/**
	 * Windowtitle
	 * @var string
	 */
	private $title = 'ModalWindow';

	/**
	 * Content
	 * @var string
	 */
	private $content = 'No content set';

	/**
	 * Set title of window
	 * @param string $title
	 * @return \Core\Lib\Content\Html\Controls\ModalWindow
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * Sets content of window
	 * @param string $content
	 * @return \Core\Lib\Content\Html\Controls\ModalWindow
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Builds and returns modal window html
	 * @return string
	 */
	public function build()
	{
		$html = '
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="modal-title">' . $this->title . '</h4>
				</div>
				<div class="modal-body" id="modal-content">' . $this->content . '</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary">Save changes</button>
				</div>
			</div>
		</div>';

		return $html;
	}
}
