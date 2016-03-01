<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

class LogController extends Controller
{
    public function Index() {

        $this->setVar([
            'headline' => $this->text('logs.headline'),
            'logs' => $this->getController()->run('Logs')
        ]);

        $this->setAjaxTarget('#core-admin');
    }


    public function Logs($entries=null) {

        \FB::log($this->cfg());

        if (!$entries) {
            $entries = $this->cfg('log.display.entries');
        }

        $data = $this->model->getLogs($entries);

        $this->setVar([
            'logs' => $data,
        ]);

        $this->setAjaxTarget('#core-admin-logs');
    }

}

