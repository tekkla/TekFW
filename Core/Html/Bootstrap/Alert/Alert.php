<?php
namespace Core\Html\Bootstrap\Alert;

use Core\Html\HtmlAbstract;
use Core\Html\Elements\Div;
use Core\Html\Bootstrap\BootstrapContextInterface;
use Core\Html\HtmlBuildableInterface;
use Core\Html\HtmlException;

/**
 * Alert.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Alert implements HtmlBuildableInterface, BootstrapContextInterface
{

    /**
     *
     * @var boolean
     */
    private $dismissable = true;

    /**
     *
     * @var string
     */
    private $type = 'primary';

    /**
     *
     * @var string
     */
    private $content = '';

    /**
     *
     * @var \Core\Html\HtmlAbstract
     */
    public $html;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->html = new Div();
    }

    /**
     * Sets dismissable state
     *
     * @param boolean $dismissable
     */
    public function setDismissable($dismissable)
    {
        $this->dismissable = (bool) $dismissable;
    }

    /**
     * Returns dismissable state
     *
     * @return boolean
     */
    public function getDismissable()
    {
        return $this->dismissable;
    }

    /**
     * Sets a different Html element to use for the alert
     *
     * @param HtmlAbstract $element
     */
    public function setHtmlElement(HtmlAbstract $element)
    {
        $this->html = $element;
    }

    /**
     * Sets Bootstrap contextual alert type
     *
     * @param string $type
     *            One of Bootstrap contextual types like primary, info, succes, warning or danger.
     *
     * @throws HtmlException
     */
    public function setType($type)
    {
        $allowed = [
            self::SUCCESS,
            self::PRIMARY,
            self::INFO,
            self::WARNING,
            self::DANGER
        ];

        if (! in_array($type, $allowed)) {
            Throw new HtmlException('Given "%s" is no valid Bootstrap::Alert type. Allowed types are: %s', $type, implode(', ', $allowed));
        }

        $this->type = $type;
    }

    /**
     * Returns set Bootstrap contextual alert type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the alerts content
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Returns set alert content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Html\HtmlBuildableInterface::build()
     */
    public function build()
    {
        if (! $this->html instanceof HtmlAbstract) {
            Throw new HtmlException('Bootstrap Alert object need a html object that is an instance of HtmlAbstact');
        }

        $this->html->addCss([
            'alert',
            'alert-' . $this->type
        ]);

        if ($this->dismissable) {
            $this->html->addCss('alert-dismissable');
            $this->html->setInner('<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>');
        }

        $this->html->addInner($this->content);
        $this->html->setRole('alert');

        return $this->html->build();
    }
}