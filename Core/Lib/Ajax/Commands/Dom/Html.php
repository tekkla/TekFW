<?php
namespace Core\Lib\Ajax\Commands\Dom;

use Core\Lib\Ajax\AjaxCommandAbstract;
use Core\Lib\Ajax\AjaxException;

/**
 * Html.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class Html extends AjaxCommandAbstract
{

    /**
     *
     * @var string
     */
    protected $type = 'dom';

    /**
     *
     * @var string
     */
    protected $fn = 'html';

    /**
     * Create a HTML ajax which changes the html of target selector
     *
     * @param string $selector
     *            Selector to be changed
     * @param string $content
     *            Content be used
     * @param string $function
     *            Optional mode how to change the selected element. Can be: 'html' (default), 'append' or 'prepend'.
     *
     * @throws \Core\Lib\Ajax\AjaxException
     */
    public function html($selector, $content, $function = 'html')
    {
        $allowed_functions = [
            'html',
            'prepend',
            'append'
        ];

        if (! in_array($function, $allowed_functions)) {
            throw new AjaxException(sprintf('"%s" is not an allowed function for a html ajax command. Allowed are: %s', $function, implode(', ', $allowed_functions)));
        }

        $this->selector = $selector;
        $this->args = $content;
    }
}
