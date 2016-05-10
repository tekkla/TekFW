<?php
namespace Core\Html;

use function Core\arrayIsAssoc;

/**
 * HtmlFactory.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class HtmlFactory
{

    /**
     * Creates an html control / element / form element by using DI container instance method
     *
     * Injects an instance of the HtmlFactory so the created html object can use it to create.
     *
     * @param string $class
     *            Short NS to used class like 'Controls\Button' or 'Elements\Div' or 'Form\Input'.
     * @param array $args
     *            Optional assoc arguments array to be used as $html->$method($value) call.
     *
     * @throws HtmlException
     *
     * @return HtmlAbstract
     */
    public function create($class, $args = [])
    {
        $html = $this->di->instance('\Core\Html\\' . $class);

        $html->factory = $this;

        foreach ($args as $method => $arg) {

            if (! method_exists($html, $method)) {
                Throw new HtmlException('Html object has no "' . $method . '" method.');
            }

            if (is_array($arg)) {

                if (! arrayIsAssoc($arg)) {
                    Throw new HtmlException('Arrayed arguments for html objects created by HtmlFactory have to be associative.');
                }

                foreach ($arg as $attr => $val) {
                    $html->$method($attr, $val);
                }
            }
            else {
                $html->$method($arg);
            }
        }

        return $html;
    }
}
