<?php
namespace Core\Lib\Ajax;

/**
 * AjaxCommand
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AjaxCommand
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

    /**
     * Constructor with option to parse command from definition array
     *
     * @param array $definition Definition to parse as ajax command
     */
    public function __construct(Array $definition = [])
    {
        if ($definition) {
            $this->parse($definition);
        }
    }

    /**
     * Sets the ajax command arguments array
     *
     * @param mixed $args
     *
     * @return \Core\Lib\AjaxCommand
     */
    public function setArgs($args = [])
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Adds an argument to ajax command arguments array
     *
     * @param mixed $arg
     *
     * @return \Core\Lib\AjaxCommand
     */
    public function addArg($arg)
    {
        $this->args[] = $arg;

        return $this;
    }

    /**
     * Sets ajax command group type
     *
     * @param string $type
     *
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
     * @param string $selector
     *
     * @return \Core\Lib\AjaxCommand
     */
    public function setSelector($selector)
    {
        $this->selector = $selector;

        return $this;
    }

    /**
     * Sets function type of ajax command
     *
     * @param string $fn
     *
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
        $this->di['core.ajax']->add($this);
    }

    /**
     * Parses an array based command definition and sets it's values as command properties
     *
     * @param unknown $definition
     */
    public function parse(Array $definition)
    {
        // Be sure that the commandtype isset
        if (! in_array('type', $definition)) {
            $definition['type'] = 'dom';
        } else {

            // Check for correct commandtype
            $types = [
                'dom',
                'act'
            ];

            if (! in_array($definition['type'], $types)) {
                Throw new \InvalidArgumentException('Your AjaxCommand type "' . $definition['type'] . '"is not allowed.');
            }
        }

        foreach ($definition as $property => $value) {
            if (property_exists($this, $property)) {
                if ($property == 'args' && ! is_array($value))
                    $value =[
                        $value
                    ];

                $this->{$property} = $value;
            }
        }
    }
}
