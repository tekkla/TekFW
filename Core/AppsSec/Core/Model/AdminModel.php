<?php
namespace Core\AppsSec\Admin\Model;

use Core\Lib\Amvc\Model;
use Core\Lib\Amvc\App;
use Core\Lib\Url;
use Core\Lib\String;

/**
 *
 * @author Michael "Tekkla" Zorn
 *        
 */
class AdminModel extends Model
{

    public function getApplist()
    {
        $applist = App::getLoadedApps();
        
        sort($applist);
        
        $out = new \stdClass();
        
        foreach ($applist as $app_name) {
            $app = App::create($app_name);
            
            $app_data = new \stdClass();
            
            $app_dataconfig_link = isset($appconfig) ? Url::factory('admin_app_config')->setParameter('app_name', String::uncamelize($app_name))->getUrl() : false;
            
            $out{$app_name} = $app_data;
        }
        
        return $out;
    }
}

