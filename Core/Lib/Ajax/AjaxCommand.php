<?php
namespace Core\Lib;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *         @Inject ajax
 */
abstract class AjaxCommand
{

    /**
     * Kind of command
     * 
     * @var string
     */
    private $type = 'dom';

    /**
     * The documents DOM ID the ajax content should go in
     * 
     * @var string
     */
    private $selector = '';

    /**
     * Parameters to pass into the controlleraction
     * 
     * @var array
     */
    private $args = [];

    /**
     * The type of the current ajax.
     * 
     * @var string
     */
    private $fn = 'html';

    public function __construct($definition = array())
    {
        if ($definition)
            $this->parse($definition);
    }

    /**
     * Used to set set an array of ajax command arguments
     * 
     * @param unknown $args
     * @return \Core\Lib\AjaxCommand
     */
    public function setArgs($args = array())
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Adds an argument to ajax command
     * 
     * @param unknown $val
     * @return \Core\Lib\AjaxCommand
     */
    public function addArg($val)
    {
        $this->args[] = $val;
        return $this;
    }

    /**
     * Sets ajax command group type
     * 
     * @param $type
     * @return \Core\Lib\AjaxCommand
     */
    public function setType($type = 'dom')
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Sets DOM id of ajax command target
     * 
     * @param $target
     * @return \Core\Lib\AjaxCommand
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;
        return $this;
    }

    /**
     * Sets content of ajax command
     * 
     * @param $content
     * @return \Core\Lib\AjaxCommand
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Sets function type of ajax command
     * 
     * @param string $fn
     * @return \Core\Lib\AjaxCommand
     */
    public function setFunction($fn = 'html')
    {
        $this->fn = $fn;
        return $this;
    }

    /**
     * Returns ajax command type
     * 
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
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

    /**
     * Returns ajax command arguments
     * 
     * @return the $args
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Returns ajax command function
     * 
     * @return the $fn
     */
    public function getFn()
    {
        return $this->fn;
    }

    /**
     * Send ajax command to output queue
     */
    public function send()
    {
        $this->ajaxaddCommand();
    }

    /**
     * Parses an array based command definition and sets it's values as command properties
     * 
     * @param unknown $definition
     */
    public function parse($definition = array())
    {
        if ($definition) {
            foreach ($definition as $property => $value) {
                if (property_exists($this, $property)) {
                    if ($property == 'args' && ! is_array($value))
                        $value = array(
                            $value
                        );
                    
                    $this->{$property} = $value;
                }
            }
        }
    }
}

