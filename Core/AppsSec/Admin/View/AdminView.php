<?php
namespace Core\AppsSec\Admin\View;

use Core\Lib\Amvc\View;

/**
 *
 * @author Michael
 *        
 */
class AdminView extends View
{

    public function Index()
    {
        echo '<hTekFW Framework Config</h';
		
		echo '
		<div class="row
			<div class="col-sm-6
				<div class="panel panel-default
					<div class="panel-body
						<a class="btn btn-default" href="', ->config, 
        'Framework Config</
						<h<stronApplications:</stron</h
						<ul class="list-group';
		
		foreach ( ->loaded_apps as $app_name => $app )
			
        echo '
							<li class="list-group-item clearfix', $app_name, ($appconfig_link ? '<a href="' . $appconfig_link . '" class="btn btn-default btn-xs pull-right<i class="fa fa-cog</</</l' : '');
        
        echo '
						</u
					</di
				</di
			</di
		</di';
    }
}

