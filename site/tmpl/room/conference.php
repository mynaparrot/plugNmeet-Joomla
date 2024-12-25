<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Mynaparrot\Component\Plugnmeet\Administrator\Helper\plugNmeetConnect;

defined('_JEXEC') or die;

$app      = Factory::getApplication();
$jsFiles  = [];
$cssFiles = [];
try
{
	$connect  = new plugNmeetConnect();
	$files    = $connect->getClientFiles();
	$jsFiles  = $files->getJSFiles();
	$cssFiles = $files->getCSSFiles();
}
catch (Exception $e)
{
	$app->enqueueMessage($e->getMessage());
	$app->redirect("/");

	return;
}

if (empty($jsFiles) || empty($cssFiles))
{
	$app->enqueueMessage(Text::_("COM_PLUGNMEET_EMPTY_CLIENT_FILES"));
	$app->redirect("/");

	return;
}

$path = $this->params->get("plugnmeet_server_url") . "/assets";

$jsTag = "";
foreach ($jsFiles as $file)
{
	$jsTag .= '<script src="' . $path . '/js/' . $file . '" defer="defer"></script>' . "\n\t";
}

$cssTag = "";
foreach ($cssFiles as $file)
{
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
