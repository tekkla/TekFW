<?php
namespace Core\Lib\Ajax\Commands\Dom;

use Core\Lib\Ajax\AjaxCommand;

/**
 * Html.php
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class Html extends AjaxCommand
{

    protected $type = 'dom';

    protected $fn = 'html';

    /**
     * Create a HTML ajax which changes the html of target selector
     *
     * @param $selector Selector to be changed
     * @param $content Content be used
     * @param $function Optional mode how to change the selected element. Can be: html(default), append or prepend.
     */
    public function html($selector, $content, $function = 'html')
    {
        $allowed_functions = [
            'html',
            'prepend',
            'append'
        ];

        if (! in_array($function, $allowed_functions)) {
            throw new \RuntimeException(sprintf('"%s" is not an allowed function for a html ajax command. Allowed are: %s', $function, implode(', ', $allowed_functions)));
        }

        $this->selector = $selector;
        $this->args = $content;
    }
}
