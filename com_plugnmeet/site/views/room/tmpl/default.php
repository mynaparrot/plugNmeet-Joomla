<?php
/**
 * @package 	plugNmeet
 * @subpackage	default.php
 * @version		1.0.4
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


?>


<!--[JCBGUI.site_view.default.28.$$$$]-->
<?php
$itemId = $this->app->input->get("Itemid", 0);
$config = JFactory::getConfig();
$isActiveSEF = $config->get('sef');

$link = "index.php?option=com_plugnmeet&view=room&id=" . $this->item->id . "&Itemid=" . $itemId . "&access_token=";

if ($isActiveSEF) {
    $link = JRoute::_("index.php?option=com_plugnmeet&view=room&id=" . $this->item->alias . "&Itemid=" . $itemId . "&access_token=");
}
?>
<div class="pnm-container">
    <div class="column column-full">
        <h1 class="headline"><?php echo $this->item->room_title; ?></h1>
        <div class="br">
            <div class="br-inner"></div>
        </div>
        <div class="description"><?php echo $this->item->description; ?></div>
        <hr/>
        <div class="column-full ">
            <div class="flex">
                <div class="w-6-4 description">
                    <?php echo JText::_("COM_PLUGNMEET_INSTRUCTIONS"); ?>
                </div>
                <div class="w-6-2">
                    <h1 class="headline"><?php echo JText::_("COM_PLUGNMEET_LOGIN"); ?></h1>
                    <div class="br">
                        <div class="br-inner"></div>
                    </div>
                    <form action="index.php" method="post" class="login-form" id="joinPlugNmeet">
                        <div id="roomStatus" class="alert" role="alert" style="display: none"></div>
                        <label for="name" class="input">
                            <p><?php echo JText::_("COM_PLUGNMEET_NAME"); ?></p>
                            <input type="text" placeholder="<?php echo JText::_("COM_PLUGNMEET_YOUR_FULL_NAME"); ?>"
                                   class="form-control"
                                   name="name"
                                   id="name" value="" required>
                        </label>
                        <label for="password" class="input">
                            <p><?php echo JText::_("COM_PLUGNMEET_PASSWORD"); ?></p>
                            <input type="password" placeholder="<?php echo JText::_("COM_PLUGNMEET_ROOM_PASSWORD"); ?>"
                                   name="password" class="form-control"
                                   id="password" required>
                        </label>

                        <div class="btns">
                            <button type="submit"
                                    class="submit"><?php echo JText::_("COM_PLUGNMEET_SUBMIT"); ?></button>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $this->item->id; ?>">
                        <input type="hidden" name="option" value="com_plugnmeet">
                        <input type="hidden" name="view" value="room">
                        <input type="hidden" name="task" value="room.loginToPNM">
                        <?php echo JHtml::_('form.token'); ?>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery("document").ready(function ($) {
        $("#joinPlugNmeet").on("submit", function (e) {
            e.preventDefault();

            const data = $(this).serialize();
            const status = $("#roomStatus");

            $.ajax({
                url: "index.php",
                method: "POST",
                data: data,
                beforeSend: () => {
                    status.show();
                    status.removeClass("alert-success");
                    status.removeClass("alert-danger");

                    status.addClass("alert-primary");
                    status.html("Checking...");
                },
                success: (data) => {
                    status.removeClass("alert-primary");

                    if (data.status && data.token) {
                        status.addClass("alert-success");
                        status.html("Redirecting...");
                        const url = '<?php echo $link ?>' + data.token + '&layout=conference';
                        const windowOpen = window.open(url, "_blank");
                        if (!windowOpen) {
                            setTimeout(() => {
                                window.location.href = url
                            }, 2000)
                        }
                        $("#password").val("");
                        status.hide();
                    } else {
                        status.addClass("alert-danger");
                        status.html(data.msg);
                    }
                },
                error: (jqXHR, textStatus, errorThrown) => {
                    status.removeClass("alert-primary");
                    status.addClass("alert-danger");
                    status.html(textStatus);
                }
            })
        });
    })
</script><!--[/JCBGUI$$$$]-->

