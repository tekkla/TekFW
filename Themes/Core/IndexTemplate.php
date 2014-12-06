<?php
namespace Themes\Core;

use Core\Lib\Content\Template;

class IndexTemplate extends Template
{
	function Head()
	{
		// Combine cached above and below with content
		echo '
<!DOCTYPE html>

<html>

<head>',

	$this->getTitle(),
	$this->getCss(),
	$this->getMeta(),
	$this->getOpenGraph(),
	$this->getScript('top'),
'
</head>';

	}

	function Body() {

		echo '
<body>',

	$this->getNavbar(),
	'
	<div class="container" id="breadcrumbs">',
	$this->getBreadcrumbs(),
	'</div>
	<div class="container" id="content">',
		$this->getMessages(),
		$this->getContent(),
	'</div>
	<div class="container" id="footer">FOOTER</div>',
		$this->getScript('below'),
	'</body>

</html>';

	}
}
