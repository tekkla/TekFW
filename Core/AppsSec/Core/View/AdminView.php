<?php
namespace Core\AppsSec\Core\View;

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
		echo '
        <h1>TekFW Framework Config</h1>
		<div class="row">
			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-body">
						<a class="btn btn-default" href="', $this->config, '">Framework Config</a>
						<h2><strong>Applications:</strong></h2>
						<ul class="list-group">';

						foreach ($this->loaded_apps as $app_name => $app) {
							echo '<li class="list-group-item clearfix">', $app_name, ($app->config_link ? '<a href="' . $app->config_link . '" class="btn btn-default btn-xs pull-right"><i class="fa fa-cog"></i></a></li>' : '');
						}

						echo '
						</ul>
					</div>
				</div>
			</div>
		</div>';
	}
}

