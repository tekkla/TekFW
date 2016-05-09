<?php
namespace Core\Html\Bootstrap\Panel;

use Core\Html\Elements\Heading;
use Core\Html\HtmlBuildableInterface;

/**
 * PanelTitle.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class PanelTitle extends AbstractPanelElement implements HtmlBuildableInterface
{

    /**
     *
     * @var string
     */
    private $title = 'panel-title';

    /**
     *
     * @var string
     */
    private $description = '';

    /**
     *
     * @var boolean
     */
    private $description_new_line = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->html = new Heading();
        $this->html->setSize(3);
        $this->html->addCss('panel-title');
        $this->html->setInner('panel-title');
    }

    /**
     *
     * @param string $title            
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $description            
     * @param boolean $new_line            
     */
    public function setDescription($description, $new_line = false)
    {
        $this->description = $description;
        $this->description_new_line = $new_line;
    }

    /**
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     *
     * @param boolean $new_line            
     */
    public function setDescriptionOnNewLine($new_line)
    {
        $this->description_new_line = (bool) $new_line;
    }

    /**
     *
     * @return boolean
     */
    public function getDescriptionOnNewLine()
    {
        return $this->description_new_line;
    }

    /**
     *
     * {@inheritDoc}
     *
     * @see \Core\Html\Bootstrap\Panel\AbstractPanelElement::build()
     */
    public function build()
    {
        $this->html->setInner($this->title);
        
        if (! empty($this->description)) {
            if ($this->description_new_line) {
                $this->html->addInner('<br>');
            }
            
            $this->html->addInner('<small>' . $this->description . '</small>');
        }
        
        return $this->html->build();
    }
}
