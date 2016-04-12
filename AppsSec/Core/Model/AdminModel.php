<?php
namespace AppsSec\Core\Model;

use Core\Amvc\Model;

/**
 * AdminModel.php
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.de>
 * @copyright 2015
 * @license MIT
 */
class AdminModel extends Model
{

    public function getApplist()
    {
        // Get list of loaded apps
        $applist = $this->di->get('core.amvc.creator')->getLoadedApps();

        // Sort he list alphabetically
        sort($applist);

        $out = [];

        // Walk through apps list and create app entry
        foreach ($applist as $app_name) {

            // Check app for existing config
            $app = $this->di->get('core.amvc.creator')->getAppInstance($app_name);

            if (! $app->hasConfig()) {
                continue;
            }

            // Link only when config for app exists
            $out[$app_name] = $this->url('config', [
                'app_name' => $this->stringUncamelize($app_name)
            ]);
        }

        return $out;
    }
}
