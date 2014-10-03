<?php
namespace Core\AppsSec\Core\View;

use Core\Lib\Amvc\View;

/**
 *
 * @author Michael "Tekkla" Zorn <tekkla@tekkla.d
 *        
 */
final class IndexView extends View
{

    public function Index()
    {
        echo '
		<header class="navbar navbar-static-top">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".bs-navbar-collapse">
						<span class="sr-onlyToggle navigation"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a href="/" class="navbar-brand">TEKFW</a>
				</div>
				<nav class="collapse navbar-collapse bs-navbar-collapse" role="navigation">
					<ul class="nav navbar-nav">
						<li>
							<a href="/core/login">Login</a>
						</li>
						<li class="active">
							<a href="/gallery">Gallery</a>
						</li>
						<li>
							<a href="/raidmanader">Raidmanager</a>
						</li>
						<li>
							<a href="../javascript">JavaScript</a>
						</li>
						<li>
							<a href="../customize">Customize</a>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li><a href="http://expo.getbootstrap.com">Expo</a></li>
						<li><a href="http://blog.getbootstrap.com">Blog</a></li>
					</ul>
				</nav>
			</div>
		</header>
		<div id="intro"></div>
		<div class="container"></div>';
    }
}

