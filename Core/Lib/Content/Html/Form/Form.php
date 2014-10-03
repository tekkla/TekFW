<?php
namespace Core\Lib\Content\Html\Form;

use Core\Lib\Abstracts\HtmlAbstract;

/**
 * Creates a form html object
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Html\Form
 * @license MIT
 * @copyright 2014 by author
 */
class Form extends HtmlAbstract
{

    protected $element = 'form';

    protected $attribute = [
        'role' => 'form',
        'method' => 'post',
        'enctype' => 'multipart/form-data'
    ];

    /**
     * Set the name of a route to compile as action url
     * 
     * @param string $route Name of route to compile
     * @param array|stdClass $params Parameter to use in route to compile
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setAction($action)
    {
        $this->attribute['action'] = $action;
        return $this;
    }

    /**
     * Set the form method attribute.
     * Use 'post' or 'get'.
     * Form elements are using post by default.
     * 
     * @param string $method Value for the method attribute of from
     * @throws NoValidParameterError
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setMethod($method)
    {
        $methods = [
            'post',
            'get'
        ];
        
        // Safety first. Only allow 'post' or 'get' here.
        if (! in_array(strtolower($method), $methods))
            Throw new \InvalidArgumentException('Wrong html form method attribute set.', 1000);
        
        $this->attribute['method'] = $method;
        return $this;
    }

    /**
     * Set the form method attribute.
     * Use 'post' or 'get'.
     * Form elements are using post by default.
     * 
     * @param string $method Value for the method attribute of from
     * @throws NoValidParameterError
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setEnctype($enctype)
    {
        $enctypes = [
            'application/x-www-form-urlencoded',
            'multipart/form-data',
            'text/plain'
        ];
        
        // Safety first. Only allow 'post' or 'get' here.
        if (! in_array(strtolower($enctype), $enctypes))
            Throw new \InvalidArgumentException('Wrong html form enctype attribute set.', 1000);
        
        $this->attribute['enctype'] = $enctype;
        return $this;
    }

    /**
     * Set form accept charset attribute
     * 
     * @param string $accept_charset
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setAcceptCharset($accept_charset)
    {
        $this->attribute['accept_charset'] = $accept_charset;
        return $this;
    }

    /**
     * Set form target attribute
     * 
     * @param string $target
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setTarget($target)
    {
        $this->attribute['target'] = $target;
        return $this;
    }

    /**
     * Set autoomplete attribute with state 'on' or 'off'
     * 
     * @param string $state
     * @throws NoValidParameterError
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function setAutocomplete($state = 'on')
    {
        $states = [
            'on',
            'off'
        ];
        
        if (! in_array(strtolower($state), $states))
            Throw new \InvalidArgumentException('Wrong html form autocomplete attribute state.', 1000);
        
        $this->attribute['autocomplete'] = $state;
        return $this;
    }

    /**
     * Deactivates form validation by setting "novalidate" attribute
     * 
     * @return \Core\Lib\Content\Html\Elements\Form
     */
    public function noValidate()
    {
        $this->attribute['novalidate'] = false;
        return $this;
    }
}
