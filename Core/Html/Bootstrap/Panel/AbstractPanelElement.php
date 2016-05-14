<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\HtmlBuildableInterface;
use Core\Html\HtmlException;

/**
 * AbstractPanelElement.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
abstract class AbstractPanelElement implements HtmlBuildableInterface
{

    /**
     *
     * @var \Core\Html\Elements\Div
     */
    public $html;

    /**
     *
     * @var array
     */
    protected $content = [];

    /**
     * Contructor
     *
     * Inits the panel elements DIV container and adds neccessary css classes like 'panel-title'
     */
    abstract public function __construct();

    /**
     * Adds content element to the content stack
     *
     * This content can be any string or an object that implements HtmlBuildableInterface.
     *
     * @param string|HtmlBuildableInterface $content            
     */
    public function addContent($content)
    {
        if (is_object($content) && ! $content instanceof HtmlBuildableInterface) {
            Throw new HtmlException('Content elements for a Bootstrap Panel object can be strings or objects that implements HtmlBuildableInterface.' . PHP_EOL . get_class($content));
        }
        
        $this->content[] = $content;
    }

    /**
     * Adds content element to the content stack after resetting the content stack
     *
     * This content can be any string or an object that implements HtmlBuildableInterface.
     *
     * @param string|HtmlBuildableInterface $content            
     */
    public function setContent($content)
    {
        $this->content = [];
        
        $this->addContent($content);
    }

    /**
     * Returns the current content stack
     *
     * @return array
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
        // No content, no output
        if (empty($this->content)) {
            Throw new HtmlException('There is no content set for this panel.');
        }
        
        foreach ($this->content as $content) {
            
            if ($content instanceof HtmlBuildableInterface || (is_object($content) && method_exists($content, 'build'))) {
                $content = $content->build();
            }
            
            $this->html->addInner($content);
        }
        
        return $this->html->build();
    }
}
