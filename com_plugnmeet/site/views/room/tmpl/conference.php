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

$params = JComponentHelper::getParams("com_plugnmeet");
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
$customLogo = "";
if ($params->get("logo")) {
    $customLogo = 'window.CUSTOM_LOGO = "' . JUri::root() . $params->get("logo") . '";';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title><?php echo $this->item->room_title; ?></title>
    <?php echo $cssTag . $jsTag; ?>

    <script type="text/javascript">
        window.PLUG_N_MEET_SERVER_URL = "<?php echo $params->get("plugnmeet_server_url"); ?>";
        window.LIVEKIT_SERVER_URL = "<?php echo $params->get("livekit_server_url"); ?>";
        window.STATIC_ASSETS_PATH = "<?php echo $path; ?>";
        <?php echo $customLogo; ?>

        Window.ENABLE_DYNACAST = <?php echo filter_var($params->get("enable_dynacast"), FILTER_VALIDATE_BOOLEAN); ?>;
        window.ENABLE_SIMULCAST = <?php echo filter_var($params->get("enable_simulcast"), FILTER_VALIDATE_BOOLEAN); ?>;
        window.STOP_MIC_TRACK_ON_MUTE = <?php echo filter_var($params->get("stop_mic_track_on_mute"), FILTER_VALIDATE_BOOLEAN); ?>;
        window.NUMBER_OF_WEBCAMS_PER_PAGE_PC = <?php echo (int)$params->get("number_of_webcams_per_page_pc"); ?>;
        window.NUMBER_OF_WEBCAMS_PER_PAGE_MOBILE = <?php echo (int)$params->get("number_of_webcams_per_page_mobile"); ?>;
    </script>
</head>
<body>
<div id="plugNmeet-app"></div>
</body>
</html>

<?php jexit(); ?>
