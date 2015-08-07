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

            $this->ajax['act'][] = [
                'f' => 'console',
                'a' => 'Your DOM ajax response from "' . $cmd->getId() . '" needs a selector but none is set'
            ];

            $this->ajax['act'][] = [
                'f' => 'console',
                'a' => $cmd->getArgs()
            ];

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
        $messages = $this->di->get('core.content.message')->getMessages();

        if ($messages) {

            foreach ($messages as $msg) {

                if ($msg->getType() == 'clear') {
                    $this->ajax['dom']['#message'][] = [
                        'f' => 'html',
                        'a' => ''
                    ];
                    continue;
                }

                $html = '
                <div class="alert alert-' . $msg->getType();

                // Message dismissable?
                if ($msg->getDismissable()) {
                    $html .= ' alert-dismissable';
                }

                // Fadeout message?
                if ($this->di->get('core.cfg')->get('Core', 'js_fadeout_time') > 0 && $msg->getFadeout()) {
                    $html .= ' fadeout';
                }

                $html .= '">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    ' . $msg->getMessage() . '
                    </div>';

                $this->ajax['dom']['#message'][] = [
                    'f' => 'append',
                    'a' => $html
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

    /**
     * Creates and returns a named ajax command.
     * Commands are split into DOM (Dom) manipulation and predifined actions (Act) to call.
     *
     * @param string $command_name Name of command to create. Default: Dom\Html
     *
     * @return \Core\Lib\Ajax\Command
     */
    public function createCommand($command_name = 'Dom\Html')
    {
        if (empty($command_name)) {
            $command_name = 'Dom\Html';
        }

        $class = '\Core\Lib\Ajax\Commands\\' . $command_name;

        return $this->di->instance($class);
    }
}
