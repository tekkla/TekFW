<?php
namespace Core\AppsSec\Doc\View;

use Core\Lib\Amvc\View;

/**
 *
 * @author Michael
 *        
 */
class MainView extends View
{

    public function Index()
    {
        echo '
		<div class="row
			<div class="col-lg-9';
        
        $this->content();
        
        echo '
			</di
			<div class="col-lg-3';
        
        $this->menu();
        
        echo '
			</di
		</di';
    }

    private function menu()
    {
        echo '
		<div id="app-doc-sidebar
			<ul class="nav';

		foreach ( ->menu as $menu )
		
        {
            echo '
				<l
					<a href="#app-doc-', $menu['node'], '', $menu['title'], '</';
            
            if ($menu['subs']) {
                echo '<ul class="nav';
                
                foreach ($menu['subs'] as $sub)
                    echo '<l<a href="#app-doc-', $menu['node'], '-', $sub['node'], '', $sub['title'], '</</l';
                
                echo '</u';
            }
            
            echo '
				</l';
        }
        
        echo '
			</u
		</di';
    }

    private function content()
    {
        
        // Intro
        echo '

		<div class="app-doc-section
			<h1 id="app-doc-welcome" class="page-headerWelcome to the TekFW framework Docs</h
			<p class="leadThe TekFW framework mod is more an enhancement than a modification for Simplemachines Forum. With TekFW you get a powerful, modern and object oriented toolkit to write and run your own apps with or within the SMF context.</
			<TekFW was developed with a clear target: Integrate new and modern webstuff into SMF without harming the general functionality of SMF itself. The plan was and is to hook onto SMFs core system, using the cool work done by so many contributors to the SMF project, and opening a door you can take but not have to.</
			<With this framework you get access to:</
			<ul class="circle
				<lSystematic OOP coding style in SMF</l
				<l</l
				<l</l
				<l</l
				<l</l
				<l</l
			</u
			<The framework is (as every framework) offers a collection of tools and mechanism which naturally won\'t be used in all it\'s depths which means to creates some "overhead". This in mind TekFW has been developed by using profiling and performance tools.</
		</di

		<div class="app-doc-section
			<h1 id="app-doc-standards" class="page-headerHow it works</h
			<p class="leadLorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<h2 id="app-doc-how-routingRouting</h
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<h2 id="app-doc-how-requestRouteres</h
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<div class="highlight

			</di
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
		</di

		<div class="app-doc-section
			<h1 id="app-doc-apps" class="page-headerApp Overview</h
			<p class="leadAn app is more than an extension or mod for SMF. It\'s a way to run complety new functionality within context of SMF. With TekFW you get a powerful tool to create easily new tools to run in SMF.</

			<h2 id="app-doc-app-structureFolders and Files</h
			<div class="app-doc-callout app-doc-callout-danger
				<hPSR-0 standard</h
				<TekFW framwork uses PSR-0 coding standard for folders, files. This means all namespaces and classes are case sensitive. Click <a href="https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md<stronthis link</stron</ to learn more about PSR-0 coding standard.</
			</di
			<Basically you need nothing more than a app mainfile it\'s app folder. But in most cases you will need more than this. Each app needs at least the following files and folders.</
			<div class="highlight<prAppName<bAppName\AppName.php</pr</di
			<There are more folders you can or must use in some cases.</
			<div class="highlight<prAppName\Controller<bAppName\Model<bAppName\View<bAppName\Lib<bAppName\Js<bAppName\Css</pr</di

			<h2 id="app-doc-app-namespaceNamespaces</h
			<TekFW uses namespaces to capsulate apps and to use an easy class autoloading. When creating an app you have to use your own and unique namespace. Within this namespace you are free to create your own folder structure. Your apps basic namespace is...</
			<div class="highlight<prApps\YourAppName</pr</di
			<When you use TekFWs MVC model, there are three additional namespaces to use...</
			<div class="highlight<prApps\YourAppName\Controller<bApps\YourAppName\Model<bApps\YourAppName\View</pr</di
			<All framework related classes have their own namespace. The basic framework namespace is..</
			<div class="highlight<prCore</pr</di

			<h2 id="app-doc-app-mainfileApp mainfile</h
			<Each app has to have a mainfile called <codYourAppName.php</cod in the <codApps\YourAppName</cod folder. In this file you can define pretty much things like using od css, js an language files or about controlling and running your app. An app mainfile could look like this:</
			<div class="highlight
				<pr';
        
        echo highlight_php_code('<?php
// Namespace
namespace Apps\YourAppName;

// Imports app lib
use Core\Lib\Amvc\App;

// Check for direct access
if (!defined(\'WEB\'))
	die(\'Cannot run without TekFW framework...\');

/**
 * Describe your class and set data you need
 * @author Your name
 * @package YourAppName
 * @subpackage Main
 * @license License type
 * @copyright Year
 * @final
 */
final class YourAppName extends App
{
	## General options

	// Flags app to load YourAppName.css from apps Css folder
	public $css = true;

	// Flags app to load YourAppName.js from apps Js folder
	public $js = true;

	// Flags app to look for language file (YourAppName.userlang.php) in apps Language folder
	public $lang = true;

	## Controlling and running

	// List of config definitions.
	public $config = [];

	// List of permissions to add
	public $perms = [];

	// List of routes used by app
	public $routes = [];

	// list of hooks used by app
	public $hooks = [];
}
');
        echo '
				</pr
			</di
			<hDescription of structure</h
			<The mainfile starts with the <codnamespace</cod declaration. As stated before it has to be a unique namespace followed by all imports of other namespaces used in our mainfile. An app has to be always a child of <codCore\Libs\App</cod so it is at least mandatory to import this namespace.</
			<Right after this you should place the direct access check to prevent running this file by requesting it via url. You do not need to check for defined SMF constant. When We constant is not defined, SMF is neither.</
			<Good code is well commented code. So describe your app, the license and waht else you think to be relevant</
			<The app class itself is a child of <codCore\Libs\App</cod and has to be set to <codfinal</cod.<

			<h2 id="app-doc-app-optionsMainclass: Options</h
			<In your mainclass you can define three generel options: <codcss</cod, <codjs</cod and <codlanguage</cod. Each of this option works like a switch which indicates to load a file on app initiation. To switch an option on you have to declare it like in the example mainclass above. When switched to on, the app will look for a file in the corresponding folder inside the folder of the app.</
			<Setting <cod$css</cod to <codtrue</cod =&gt; App will look for: <codApps\YourAppName\Css\YourAppName.css</cod</
			<Setting <cod$js</cod to <codtrue</cod =&gt; App will look for: <codApps\YourAppName\Js\YourAppName.js</cod</
			<Setting <cod$lang</cod to <codtrue</cod =&gt; App will look for: <codApps\YourAppName\Language\YourAppName.{userlanguage}.php</cod</

			<h2 id="app-doc-app-optionsMainclass: Options</h

			<div class="highlight<prApps\YourAppName\Controller<bApps\YourAppName\Model<bApps\YourAppName\View</pr</di
			<All framework related classes have their own namespace. The basic framework namespace is..</

			<div class="highlight<prCore</pr</di

			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<h2 id="app-doc-app-structureApp structure</h
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
			<Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</
		</di';
    }
}
