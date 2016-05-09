<?php
namespace Core\Html\Bootstrap;

/**
 * BootstrapContextInterface.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2016
 * @license MIT
 */
interface BootstrapContextInterface
{
    const PRIMARY = 'primary';

    const SUCCESS = 'success';

    const INFO = 'info';

    const WARNING = 'warning';

    const DANGER = 'danger';

    /**
     * Sets Bootstrap color context
     *
     * @param string $context Bootstrap contextual color like primary, success, info, warning or danger
     */
    public function setContext($context);

    /**
     * Returns set Bootstrab contextual color
     *
     * @return string
     */
    public function getContext();
}
