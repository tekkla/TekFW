<?php
namespace Core\Lib\Ajax;

/**
 * Ajax commands which are managed by framework.js
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
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

    public function __construct()
    {}

    /**
     * Builds ajax definition and adds it to the ajaxlist
     */
    public function add(AjaxCommand $cmd)
    {
        // Create alert on missing target when type is in need-target list
        if ($cmd->getType() == 'dom' && ! $cmd->getSelector()) {

            $this->fnConsole('Your DOM ajax response needs a selector but none is set. Aborting.');
            $this->fnConsole($cmd->getArgs());
            return;
        }

        // Publish ajax definition to ajaxlist
        $ajax = [
            'f' => $cmd->getFn(),
            'a' => $cmd->getArgs()
        ];

        if ($cmd->getType() == 'dom') {
            $this->ajax['dom'][$cmd->getSelector()][] = $ajax;
        }
        else {
            $this->ajax['act'][] = $ajax;
        }
    }

    /**
     * Builds the ajax command structure
     */
    public function process()
    {

        // Add messages
        $messages = $this->di['core.content.message']->getMessages();

        if ($messages) {

            foreach ($messages as $message) {

                $this->ajax['dom']['#message'] = [
                    'a' => $message->build(),
                    'f' => 'append'
                ];
            }
        }

        // Output is json encoded
        return json_encode($this->ajax);
    }

    /**
     * Returns the complete ajax command stack as it is
     *
     * @return array
     */
    public function getCommandStack()
    {
        return $this->ajax;
    }

    // # PREDEFINED METHODS ##############################################################################################

    /**
     * Create an msgbox in browser
     *
     * @param $msg
     */
    public function fnAlert($msg)
    {
        $this->ajax['act'][] = [
            'f' => 'alert',
            'a' => $msg
        ];
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
        $this->ajax['dom'][$selector] = [
            'f' => 'html',
            'a' => $content
        ];
    }

    /**
     * Send an error to the error div
     *
     * @param unknown_type $error
     */
    public function fnError($error, $id)
    {
        $this->ajax['act'][] = [
            'f' => 'error',
            'a' => [
                $error,
                $id
            ]
        ];
    }

    /**
     * Change a DOM attribute
     */
    public function fnAttrib($selector, $attribute, $value)
    {
        $this->ajax['dom'][$selector] = [
            'f' => 'attr',
            'a' => array(
                $attribute,
                $value
            )
        ];
    }

    /**
     * Change css property of dom element
     */
    public function fnCss($selector, $property, $value)
    {
        $this->ajax['dom'][$selector] = [
            'f' => 'css',
            'a' => array(
                $property,
                $value
            )
        ];
    }

    /**
     * Change css property of dom element
     */
    public function fnAddClass($selector, $class)
    {
        $this->ajax['dom'][$selector] = [
            'f' => 'addClass',
            'a' => $class
        ];
    }

    /**
     * Calls a page refresh by loading the provided url.
     * Calls location.href="url" in page.
     *
     * @param string|Url $url Can be an url as string or an Url object on which the getUrl() method is called
     */
    public function fnRefresh($url)
    {
        $this->ajax['act'][] = [
            'f' => 'refresh',
            'a' => $url
        ];
    }

    /**
     * Creates ajax response to load a js file.
     *
     * @param string $file Complete url of file to load
     */
    public function fnLoadScript($file)
    {
        $this->ajax['act'][] = [
            'f' => 'load_script',
            'a' => $file
        ];
    }

    /**
     * Create console log output
     *
     * @param string $msg
     */
    public function fnConsole($msg)
    {
        $this->ajax['act'][] = [
            'f' => 'console',
            'a' => $msg
        ];
    }

    /**
     * Creates a print_r console output of provided $var
     *
     * @param mixed $var
     */
    public function fnPrintVar($var)
    {
        $this->ajax['act'][] = [
            'f' => 'dump',
            'a' => print_r($var, true)
        ];
    }

    /**
     * Creates a var_dump console output of provided $var
     *
     * @param mixed $var
     */
    public function fnDumpVar($var)
    {
        ob_start();

        var_dump($var);

        $this->ajax['act'][] = [
            'f' => 'dump',
            'a' => ob_get_clean(),
        ];
    }
}
