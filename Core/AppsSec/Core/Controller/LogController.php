<?php
namespace Core\AppsSec\Core\Controller;

use Core\Lib\Amvc\Controller;

class LogController extends Controller
{

    public function Index()
    {
        $this->setVar([
            'headline' => $this->text('logs.headline'),
            'logs' => $this->getController()
                ->run('Logs')
        ]);

        $this->setAjaxTarget('#core-admin');
    }

    public function Logs($entries = null)
    {


        if (! $entries) {
            $entries = $this->cfg('log.display.entries');
            $entries = 20;
        }

        $data = $this->model->getLogs($entries);

        $this->setVar([
            'logs' => $data
        ]);

        $this->setAjaxTarget('#core-admin-logs');
    }
}

