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

$assetsPath = $this->params->get("plugnmeet_server_url") . "/assets";

$jsTags        = "";
$jsTagsPreload = "";
foreach ($jsFiles as $file)
{
	$jsTags .= '<script src="' . $assetsPath . '/js/' . $file . '" defer="defer"></script>' . "\n";
	if (str_contains($file, "runtime") || str_contains($file, "vendor"))
	{
		$jsTagsPreload .= '<link href="' . $assetsPath . '/js/' . $file . '" rel="preload" as="script" />' . "\n\t";
	}
}

$cssTags        = "";
$cssTagsPreload = "";
foreach ($cssFiles as $file)
{
	$cssTags .= '<link href="' . $assetsPath . '/css/' . $file . '" rel="stylesheet" />' . "\n\t";
	if (str_contains($file, "vendor"))
	{
		$cssTagsPreload .= '<link href="' . $assetsPath . '/css/' . $file . '" rel="preload" as="style" />' . "\n\t";
	}
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title><?php echo $this->item->room_title; ?></title>
	<?php echo $cssTagsPreload . $jsTagsPreload . $cssTags . $this->getGlobalVariables(); ?>
</head>
<body>
<div id="plugNmeet-app"></div>
<?php echo $jsTags; ?>
</body>
</html>

<?php jexit(); ?>
