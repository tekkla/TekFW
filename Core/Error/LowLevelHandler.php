<?php
namespace Core\Error;

/**
 * LowLevelHandler.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
class LowLevelHandler extends HandlerAbstract
{
    /**
     *
     * @var \Throwable
     */
    private $t;

    public function run(\Throwable $t)
    {
        $this->t = $t;

        if (ini_get('display_errors') == 1) {
            return '
            <h1>Error</h1>
            <p><strong>' . $this->t->getMessage() . '</strong></p>
            <p>in ' . $this->t->getFile() . ' (Line: ' . $this->t->getLine() . ')</p>
            <p><small>Handler: ' . __CLASS__ . '</small></p>';
        }

        // Store error in errlolog!
        error_log($this->t->getMessage() . ' >> ' . $this->t->getFile() . ':' . $this->t->getLine());

        // Set error http statuscode
        http_response_code(500);
    }
}
