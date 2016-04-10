<?php
namespace AppsSec\Core\Controller;

use Core\Amvc\Controller;

/**
 * IndexController.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
final class IndexController extends Controller
{
    // We do not need a model
    protected $has_no_model = true;

    public function Index()
    {
        $this->setVar('session', $_SESSION);
    }
}

