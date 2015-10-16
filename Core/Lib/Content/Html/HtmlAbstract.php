<?php
namespace Core\Lib\Content\Html;

use Core\Lib\Traits\ArrayTrait;
use Core\Lib\Traits\TextTrait;
use Core\Lib\Errors\Exceptions\InvalidArgumentException;

/**
 * HtmlAbstract.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
abstract class HtmlAbstract
{
    use ArrayTrait;
    use TextTrait;

    /**
     * Element type
     *
     * @var string
     */
    protected $element;

    /**
     * Attribute: name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Attribute: id
     *
     * @var string
     */
    protected $id = '';

    /**
     * Attribute: class
     *
     * @var array
     */
    protected $css = [];

    /**
     * Attribute: style
     *
     * @var array
     */
    protected $style = [];

    /**
     * Events
     *
     * @var array
     */
    protected $event = [];

    /**
     * Custom html attributes
     *
     * @var array
     */
    protected $attribute = [];

    /**
     * Data attributes
     *
     * @var array
     */
    protected $data = [];

    /**
     * Inner HTML of element
     *
     * @var string
     */
    protected $inner = '';

    /**
     *
     * @var HtmlFactory
     */
    public $factory;

    public function __construct()
    {}

    /**
     * Sets the element type like 'div', 'input', 'p' etc
     *
     * @param string $element
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function setElement($element)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Returns element type
     *
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Sets the element name
     *
     * @param string $name
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * Removes element name
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function removeName()
    {
        $this->name = '';

        return $this;
    }

    /**
     * Returns name if set.
     * No name set it returns boolean false.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the id of the element
     *
     * @param string $id
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function setId($id)
    {
        $this->id = (string) $id;

        return $this;
    }

    /**
     * Returns the id of the element
     *
     * @return string|boolean
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Removes id from elements
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function removeId()
    {
        $this->id = '';

        return $this;
    }

    /**
     * Sets inner value of element like.
     *
     * <code>
     * &lt;div&gt;{inner}&lt;/div&gt;
     * </code>
     *
     * @param unknown $inner
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function setInner($inner)
    {
       $this->inner = (string) $inner;

        return $this;
    }

    /**
     * Adds content to existing inner conntent.
     *
     * @param string $content
     *
     * @return \Core\Lib\Abstracts\HtmlAbstract
     */
    public function addInner($content)
    {
        $this->inner .= (string) $content;

        return $this;
    }

    /**
     * Returns inner value if set.
     * No set returns boolean false.
     *
     * @return string|boolean
     */
    public function getInner()
    {
        return $this->inner;
    }

    /**
     * Sets html title attribute.
     *
     * Tries to load string from txt storage when argument begins with "txt-".
     *
     * @param string $title
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function setTitle($title)
    {
        $this->addAttribute('title', (string) $title);

        return $this;
    }

    public function setTabindex($index)
    {
        $this->addAttribute('tabindex', (int) $index);

        return $this;
    }

    /**
     * Add one or more css classes to the html object.
     * Accepts single value, a string of space separated classnames or an array of classnames.
     *
     * @param string|array $css
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function addCss($css)
    {
        if (! is_array($css)) {

            // Clean css argument from unnecessary spaces
            $css = preg_replace('/[ ]+/', ' ', $css);

            // Do not trust the programmer and convert a possible
            // string of multiple css class notations to array
            $css = explode(' ', $css);
        }

        foreach ($css as $class) {
            $this->css[$class] = $class;
        }

        return $this;
    }

    /**
     * Checks for the existance of a css property in a html object or for a css class / array of css classes in the css property
     *
     * @param string array $css Optional parameter can be a single css class as string or a list of classes in an array
     *
     * @return boolean
     */
    public function checkCss($check = null)
    {
        // Css (could be array) and objects css property set?
        if (isset($check) && $this->css) {

            // convert non array css to array
            if (! is_array($check)) {
                $check = (array) $check;
            }

            // Is css to check already in objects css array?
            $check = array_intersect($check, $this->css) ? true : false;
        }
        else {
            // Without set css param we only check if css is used
            $check = $this->css ? true : false;
        }

        return $check;
    }

    /**
     * Returns set css values.
     * Returns boolean false if css is empty.
     *
     * @return array
     */
    public function getCss()
    {
        return $this->css ? $this->css : false;
    }

    /**
     * Adds a single style or an array of styles to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function addStyle()
    {
        $type = func_num_args() == 1 ? 'pair_array' : 'pair_one';
        $this->addTo(func_get_args(), $type);

        return $this;
    }

    /**
     * Removes a style from the styles collection
     *
     * @param string $style
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function removeStyle($style)
    {
        if (isset($this->style[$style])) {
            unset($this->style[$style]);
        }

        return $this;
    }

    /**
     * Adds a single event or an array of events to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function addEvent()
    {
        $this->addTo(func_get_args());

        return $this;
    }

    /**
     * Adds a single style or an array of styles to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     * This method takes care of single attributes like "selected" or "disabled".
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function addAttribute()
    {
        $this->addTo(func_get_args());

        return $this;
    }

    /**
     * Removes an attribute
     */
    public function removeAttribute($name)
    {
        if (isset($this->attribute[$name])) {
            unset($this->attribute[$name]);
        }

        return $this;
    }

    /**
     * Returns the requests attributes value.
     *
     * @param string $attribute
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function getAttribute($attribute)
    {
        if (! isset($this->attribute[$attribute])) {
            Throw new InvalidArgumentException(sprintf('The requested attribute "%s" does not exits in this html element "%s".', $attribute, get_called_class()));
        }
        else {
            return $this->attribute[$attribute];
        }
    }

    /**
     * Check for an set attribute
     *
     * @param string $attribute
     */
    public function checkAttribute($attribute)
    {
        return isset($this->attribute[$attribute]);
    }

    /**
     * Adds a single data attribute or an array of data attributes to the element.
     * Although no parameters are visible the method handles two different
     * types of parameter. Set two params for "key" and "value" or an array
     * with a collection of keys and values.
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function addData()
    {
        $this->addTo(func_get_args());

        return $this;
    }

    /**
     * Returns the value of the requested data attribute.
     * Returns boolean false if data attribute is not set.
     *
     * @param string $key
     *
     * @return string|boolean
     */
    public function getData($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : false;
    }

    /**
     * Checks the existance of a data attribute
     *
     * @param string $key
     *
     * @return boolean
     */
    public function checkData($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Removes a data ttribute
     *
     * @param string $key
     *
     * @return \Core\Lib\HtmlAbstract
     */
    public function removeData($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }

        return $this;
    }

    /**
     * Adds single and multiple elements to properties.
     *
     * @param unknown $args
     *
     * @param unknown $type
     */
    private function addTo($args)
    {
        $dt = debug_backtrace();
        $func = strtolower(str_replace('add', '', $dt[1]['function']));

        if (! isset($this->{$func}) || (isset($this->{$func}) && ! is_array($this->$func))) {
            $this->{$func} = [];
        }

        // Do we have one argument or two?
        if (count($args) == 1) {

            // One argument and not an array means we have one single value to add
            // This is when you set attributes without values like selected, disabled etc.
            if (! is_array($args[0])) {
                $this->{$func}[$args[0]] = false;
            }
            else {
                // Check the arguments for assoc array and add arguments according to the
                // result of check as key, val or only as val
                if ($this->arrayIsAssoc($args[0])) {
                    foreach ($args[0] as $key => $val) {
                        $this->{$func}[$key] = $val;
                    }
                }
                else {
                    foreach ($args[0] as $val) {
                        $this->{$func}[] = $val;
                    }
                }
            }
        }
        else {
            $this->{$func}[$args[0]] = $args[1];
        }
    }

    /**
     * Builds and returns the html code created out of all set attributes and their values.
     *
     * @return string
     */
    public function build()
    {
        $html_attr = [];

        if (! $this->element) {
            $this->element = strtolower((new \ReflectionClass($this))->getShortName());
        }

        if (!empty($this->id)) {
            $html_attr['id'] = $this->id;
        }

        if (!empty($this->name)) {
            $html_attr['name'] = $this->name;
        }

        if ($this->css) {
            $this->css = array_unique($this->css);
            $html_attr['class'] = implode(' ', $this->css);
        }

        if ($this->style) {

            $styles = [];

            foreach ($this->style as $name => $val) {
                $styles[] = $name . ': ' . $val;
            }

            $html_attr['style'] = implode('; ', $styles);
        }

        if ($this->event) {
            foreach ($this->event as $event => $val) {
                $html_attr[$event] = $val;
            }
        }

        if ($this->data) {
            foreach ($this->data as $attr => $val) {
                $html_attr['data-' . $attr] = $val;
            }
        }

        if ($this->attribute) {
            foreach ($this->attribute as $attr => $val) {
                $html_attr[$attr] = $val;
            }
        }

        // we have all our attributes => build attribute string
        $tmp_attr = [];

        foreach ($html_attr as $name => $val) {
            $tmp_attr[] = $val === false ? $name : $name . (strpos($name, 'data') === false ? '="' . $val . '"' : '=\'' . $val . '\'');
        }

        $html_attr = implode(' ', $tmp_attr);

        // html attribute string has been created, lets build the element
        switch ($this->element) {

            case 'input':
            case 'meta':
            case 'img':
            case 'link':
                $html = '<' . $this->element . ($html_attr ? ' ' . $html_attr : '') . '>';
                break;

            default:
                $html = '<' . $this->element . ($html_attr ? ' ' . $html_attr : '') . '>' . $this->inner . '</' . $this->element . '>';
                break;
        }

        return $html;
    }
}
