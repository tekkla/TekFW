<?php
namespace Core\Lib;

use Core\Lib\Content\Html\Controls\ModalWindow;
if (! defined('TEKFW'))
    die('Cannot run without TekFW framework...');

/**
 * Ajax commands which are managed by framework.js
 * 
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 * @package TekFW
 * @subpackage Lib
 * @license MIT
 * @copyright 2014 by author
 */
final class Ajax
{

    /**
     * Storage for ajax commands
     * 
     * @var \stdClass
     */
    private $ajax = [];

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
     * Builds ajax definition and adds it to the ajaxlist
     */
    public function add(\Core\Lib\AjaxCommand $cmd)
    {
        // Create alert on missing target when type is in need-target list
        if ($cmd->getType() == 'dom' && ! $cmd->getSelector()) {
            self::console('Your DOM ajax response needs a selector but none is set. Aborting.');
            return;
        }
        
        // Create modal content on type of modal
        if ($this->fn == 'modal') {
            $modal = ModalWindow::factory();
            $modal->setContent($this->content);
            
            if (isset($this->cmd_vars['title']))
                $modal->setTitle($this->cmd_vars['title']);
            
            $this->args = $modal->build();
        }
        
        $cmd = new \stdClass();
        
        $cmd->f = $this->fn;
        $cmd->a = is_array($this->args) ? $this->args : array(
            $this->args
        );
        
        // Publish ajax definition to ajaxlist
        if ($this->type == 'dom') {
            $cmd->s = $this->selector;
            
            self::$ajax['dom'][$this->selector][] = $cmd;
        } else
            self::$ajax['act'][] = $cmd;
    }

    /**
     * Builds the ajax command structure
     */
    public function process()
    {
        // Add messages
        $messages = Message::getMessages();
        
        if ($messages) {
            foreach ($messages as $message)
                self::command(array(
                    'type' => 'dom',
                    'args' => $message->build(),
                    'selector' => '#message',
                    'fn' => 'append'
                ));
        }
        
        // Output is json encoded
        return json_encode(self::$ajax);
    }

    /**
     * Returns the complete ajax command stack as it is
     * 
     * @return array
     */
    public function getCommandStack()
    {
        return self::$ajax;
    }
    
    // # PREDEFINED METHODS ##############################################################################################
    
    /**
     * Create an msgbox in browser
     * 
     * @param $msg
     */
    public function fnAlert($msg)
    {
        self::command([
            'type' => 'act',
            'fn' => 'alert',
            'args' => $msg
        ]);
    }

    /**
     * Start a controller run
     * 
     * @param $ctrl
     * @param $action
     * @param $target
     */
    public function fnCall($app_name, $controller, $action, $target = '', $params = array())
    {
        self::command([
            'selector' => $target,
            'args' => App::create($app_name)->getController($controller)->run($action, $params)
        ]);
    }

    /**
     * Create a HTML ajax which changes the html of target selector
     * 
     * @param $target Selector to be changed
     * @param $content Content be used
     * @param $mode Optional mode how to change the selected element. Can be: replace(default) | append | prepend | remove | after | before
     */
    public function fnHtml($selector, $content)
    {
        self::command([
            'selector' => $selector,
            'args' => $content
        ]);
    }

    /**
     * Send an error to the error div
     * 
     * @param unknown_type $error
     */
    public function fnError($error)
    {
        self::command(array(
            'selector' => '#message',
            'fn' => 'append',
            'args' => 'error'
        ));
    }

    /**
     * Change a DOM attribute
     * 
     * @param $target => DOM id
     * @param $attribute => attribute name
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove
     * @todo
     *
     */
    public function fnAttrib($selector, $attribute, $value)
    {
        self::command(array(
            'type' => 'dom',
            'selector' => $selector,
            'fn' => 'attr',
            'args' => array(
                $attribute,
                $value
            )
        ));
    }

    /**
     * Change css property of dom element
     * 
     * @param $target => DOM id
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove
     */
    public function fnCss($selector, $property, $value)
    {
        self::command(array(
            'type' => 'dom',
            'selector' => $selector,
            'fn' => 'css',
            'args' => array(
                $property,
                $value
            )
        ));
    }

    /**
     * Change css property of dom element
     * 
     * @param $target => DOM id
     * @param $content
     * @param $mode optional => the edit mode replace(default)|append|prepend|remove
     */
    public function fnAddClass($selector, $class)
    {
        self::command(array(
            'type' => 'dom',
            'selector' => $selector,
            'fn' => 'addClass',
            'args' => $class
        ));
    }

    /**
     * Calls a page refresh by loading the provided url.
     * Calls location.href="url" in page.
     * 
     * @param string|Url $url Can be an url as string or an Url object on which the getUrl() method is called
     */
    public function fnRefresh($url)
    {
        if ($url instanceof Url)
            $url = $url->getUrl();
        
        self::command(array(
            'type' => 'act',
            'fn' => 'refresh',
            'args' => $url
        ));
    }

    /**
     * Creates ajax response to load a js file.
     * 
     * @param string $file Complete url of file to load
     */
    public function fnLoadScript($file)
    {
        self::command(array(
            'type' => 'act',
            'fn' => 'load_script',
            'args' => $file
        ));
    }

    /**
     * Create console log output
     * 
     * @param string $msg
     */
    public function fnConsole($msg)
    {
        self::command(array(
            'type' => 'act',
            'fn' => 'console',
            'args' => $msg
        ));
    }

    /**
     * Creates a print_r console output of provided $var
     * 
     * @param mixed $var
     */
    public function fnDump($var)
    {
        self::command(array(
            'type' => 'act',
            'fn' => 'dump',
            'args' => print_r($var, true)
        ));
    }
}
