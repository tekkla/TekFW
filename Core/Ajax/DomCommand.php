<?php
namespace Core\Ajax;

/**
 * Dom.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class DomCommand extends AbstractAjaxCommand
{

    /**
     * jQuery.html()
     *
     * @var string
     */
    const HTML = 'html';

    /**
     * jQuery.before()
     *
     * @var string
     */
    const BEFORE = 'before';

    /**
     * jQuery.append()
     *
     * @var string
     */
    const APPEND = 'append';

    /**
     * jQuery.prepend()
     *
     * @var string
     */
    const PREPEND = 'prepend';

    /**
     * jQuery.html('')
     *
     * @var string
     */
    const CLEAR = 'html';

    /**
     * jQuery.css()
     *
     * @var string
     */
    const CSS = 'css';

    /**
     * jQuery.attr()
     *
     * @var string
     */
    const ATTR = 'attr';

    protected $type = 'dom';

    protected $fn = 'html';

    protected $selector = '';

    /**
     * Generoc private method to set needed commandproperties
     *
     * @param string $selector
     *            The selector
     * @param string $function
     *            The function to call
     * @param string $content
     *            The content to use as function argument
     */
    private function init($selector, $function, $content)
    {
        $this->setFunction(self::PREPEND);
        $this->setSelector($selector);
        $this->setArgs($content);
    }

    /**
     * jQuery($selector).html($content)
     *
     * @param string $selector
     *            The selector
     * @param string $content
     *            The content
     */
    public function html($selector, $content)
    {
        $this->init($selector, self::HTML, $content);
    }

    /**
     * jQuery($selector).append($content)
     *
     * @param string $selector
     *            The selector
     * @param string $content
     *            The content
     */
    public function append($selector, $content)
    {
        $this->init($selector, self::APPEND, $content);
    }

    /**
     * jQuery($selector).prepend($content)
     *
     * @param string $selector
     *            The selector
     * @param string $content
     *            The content
     */
    public function prepend($selector, $content)
    {
        $this->init($selector, self::PREPEND, $content);
    }

    /**
     * jQuery($selector).html('');
     *
     * @param string $selector
     *            The selector
     */
    public function clear($selector)
    {
        $this->init($selector, self::CLEAR, '');
    }


    /**
     * jQuery($selector).css($property, $value)
     *
     * @param string $selector
     *            The selector
     * @param string $property
     *            The property
     * @param mixed $value
     *            The value
     */
    public function css($selector, $property, $value)
    {
        $this->init($selector, self::CSS, [
            $property,
            $value
        ]);
    }

    /**
     * jQuery($selector).attr($property, $value)
     *
     * @param string $selector
     *            The selector
     * @param string $property
     *            The property
     * @param mixed $value
     *            The value
     */
    public function attr($selector, $property, $value)
    {
        $this->init($selector, self::CSS, [
            $property,
            $value
        ]);
    }

    /**
     * Sets DOM id of ajax command target
     *
     * @param string $selector
     *            The selector
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
    }

    /**
     * Returns selector of ajax command
     *
     * @return the $selector
     */
    public function getSelector()
    {
        return $this->selector;
    }
}
