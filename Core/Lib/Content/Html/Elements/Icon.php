<?php
namespace Core\Lib\Content\Html\Elements;

use Core\Lib\Abstracts\HtmlAbstract;

/**
 * Creates a icon object of type 'i' which is not an offical html element.
 * This classs provides a way to create fontawesome icons like it is
 * possible on regular html elements.
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Element
 * @license MIT
 * @copyright 2014 by author
 */
class Icon extends HtmlAbstract
{
	/**
	 * Icon name
	 * @var string
	 */
	private $icon;

	/**
	 * Size of icon
	 * @var string
	 */
	private $size;

	/**
	 * Icon this icon will stack on
	 * @var Icon
	 */
	private $on;

	/**
	 * Float direction
	 * @var string left | right
	 */
	private $pull;

	/**
	 * Draw a border around icon?
	 * @var boolean
	 */
	private $border = false;

	/**
	 * Set icon as muted?
	 * @var boolean
	 */
	private $muted = false;

	/**
	 * Degree to rotate the icon
	 * @var int
	 */
	private $rotation;

	/**
	 * Icon flip orientation
	 * @var string
	 */
	private $flip;

	/**
	 * Spinning flag
	 * @var boolean
	 */
	private $spin = false;
	protected $element = 'i';
	protected $css = ['fa'];


	public function __construct($icon='')
	{
		if (!$icon)
			$this->icon = $icon;
	}

	/**
	 * Iconname of icon to use
	 * @param string $icon
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function useIcon($icon)
	{
		$this->icon = $icon;
		return $this;
	}

	/**
	 * Sets the size of our icon.
	 * Sizes are 'large', '2x', '3x' and '4x'. All other sizes will throw an error
	 * @param string $size
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function setSize($size)
	{
		// sizes which are allowed
		$sizes = array(
			'lg',
			'2x',
			'3x',
			'4x'
		);

		if (!in_array($size, $sizes))
			Throw new \InvalidArgumentException('Wrong size set.', 1000);

		$this->size = $size;
		return $this;
	}

	/**
	 * Flags icon to have a fixed with
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function useFixedWidth()
	{
		$this->addCss('fa-fixed-width');
		return $this;
	}

	/**
	 * Activates icon border
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function useBorder()
	{
		$this->border = true;
		return $this;
	}

	/**
	 * Set icon as muted
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function isMuted()
	{
		$this->muted = true;
		return $this;
	}

	/**
	 * Floats icon left
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function pullLeft()
	{
		$this->pull = 'left';
		return $this;
	}

	/**
	 * Floats icon right
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function pullRight()
	{
		$this->pull = 'right';
		return $this;
	}

	/**
	 * Set icon rotation degree.
	 * Select from 0, 90, 180 or 270. Value of 0 cancels rotaton.
	 * @param int $rotation
	 * @throws Error
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function setRotation($rotation)
	{
		$rotas = array(
			0,
			90,
			180,
			270
		);

		if (!in_array($rotation, $rotas))
			Throw new \InvalidArgumentException('Wrong rotation degree set.', 1000);

		if ($rotation == 0)
			unset($this->rotation);
		else
			$this->rotation = $rotation;

		return $this;
	}

	public function flipHorizontal()
	{
		$this->flip = 'horizontal';
		unset($this->rotation);
		return $this;
	}

	public function flipVertical()
	{
		$this->flip = 'vertical';
		unset($this->rotation);
		return $this;
	}

	/**
	 * Activates icon spinning
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function isSpin()
	{
		$this->spin = true;
		return $this;
	}

	/**
	 * Set an icon name to stack our icon on.
	 * The parameter needs to be a fontawesome icon name without the leading "icon-".
	 * @param string $icon
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function stackOn($icon)
	{
		$this->on = $icon;
		return $this;
	}

	/**
	 * Define icon a non stacked one
	 * @return \Core\Lib\Content\Html\Elements\Icon
	 */
	public function noStack()
	{
		unset($this->on);
		return $this;
	}

	/**
	 * Icon creation
	 * @see \Core\Lib\Html::build()
	 */
	public function build()
	{
		// first step is to set the icon name itself
		$this->addCss('fa-' . $this->icon);

		if (isset($this->on))
		{
			$stack = Span::factory();
			$stack->addCss('fa fa-stack');
			$this->addCss('fa-stack-1x');

			// Create the on icon
			$on = Icon::factory($this->on);
			$on->addCss(array(
				'fa-stack-2x',
				'icon_bg'
			));
			$icon_1 = $on->build();
		}

		// size set for icon?
		if (isset($this->size))
		{
			if (isset($stack))
				$stack->addCss('fa-' . $this->size);
			else
				$this->addCss('fa-' . $this->size);
		}

		// any floating wanted?
		if (isset($this->pull))
		{
			if (isset($stack))
				$stack->addCss('fa-pull-' . $this->pull);
			else
				$this->addCss('fa-pull-' . $this->pull);
		}

		// draw border?
		if ($this->border && !isset($stack))
			$this->addCss('fa-border');
		;

		// is muted?
		if ($this->muted)
		{
			if (isset($stack))
				$stack->addCss('fa-muted');
			else
				$this->addCss('fa-muted');
		}

		// flip icon?
		if (isset($this->flip))
		{
			if (isset($stack))
				$stack->addCss('fa-flip-' . $this->flip);
			else
				$this->addCss('fa-flip-' . $this->flip);
		}

		// rotate icon?
		if (isset($this->rotation))
		{
			if (isset($stack))
				$stack->addCss('fa-rotate-' . $this->rotation);
			else
				$this->addCss('fa-rotate-' . $this->rotation);
		}

		// spin icon?
		if ($this->spin)
		{
			if (isset($stack))
				$stack->addCss('fa-spin');
			else
				$this->addCss('fa-spin');
		}

		$icon_2 = parent::build();

		if (isset($stack))
		{
			$stack->setInner($icon_1 . PHP_EOL . $icon_2);
			$html = $stack->build();
		}
		else
			$html = $icon_2;

		return $html;
	}
}

