<?php
/*------------------------------------------------------------------------------|  www.mynaparrot.com  |----/
				MynaParrot
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.2
	@build			5th February, 2022
	@created		4th February, 2022
	@package		plugNmeet
	@subpackage		default.php
	@author			Jibon L. Costa <https://www.plugnmeet.com>
	@copyright		Copyright (C) mynaparrot. All Rights Reserved
	@license		MIT

	Plug N Meet

/------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$clientPath = JPATH_ROOT . "/components/com_plugnmeet/assets/client/dist/assets";
$jsFiles = preg_grep('~\.(js)$~', scandir($clientPath . "/js", SCANDIR_SORT_DESCENDING));
$cssFiles = preg_grep('~\.(css)$~', scandir($clientPath . "/css", SCANDIR_SORT_DESCENDING));

$path = JUri::root() . "components/com_plugnmeet/assets/client/dist/assets";
$jsTag = "";
foreach ($jsFiles as $file) {
    $jsTag .= '<script src="' . $path . '/js/' . $file . '" defer="defer"></script>' . "\n\t";
}

$cssTag = "";
foreach ($cssFiles as $file) {
    $cssTag .= '<link href="' . $path . '/css/' . $file . '" rel="stylesheet" />' . "\n\t";
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title><?php echo $this->item->room_title; ?></title>
    <?php echo $cssTag . $jsTag . $this->getGlobalVariables(); ?>
</head>
<body>
<div id="plugNmeet-app"></div>
</body>
</html>

<?php jexit(); ?>
