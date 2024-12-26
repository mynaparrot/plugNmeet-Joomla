<?php
defined('_JEXEC') or die;

/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user      = Factory::getApplication()->getIdentity();
$assetName = sprintf("com_plugnmeet.room.%d", $this->item->id);
?>
<form name="adminForm" id="pnmJoinForm"
      action="<?php echo Route::_('index.php?option=com_plugnmeet&view=room&task=room.join&id=' . (int) $this->item->id); ?>">
    <div class="d-flex justify-content-center mb-3">
        <div class="spinner-border" role="status" id="status" hidden="hidden">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="text-danger" id="errorMsg"></div>
    </div>
    <div class="mb-3">
        <input type="text" name="name" required class="form-control" value="<?php echo $user->name; ?>"
               placeholder="<?php echo Text::_("COM_PLUGNMEET_FULL_NAME") ?>">
    </div>
	<?php if (!$user->authorise("join.passwordless", $assetName)): ?>
        <div class="mb-3">
            <input type="password" name="password" required class="form-control"
                   placeholder="<?php echo Text::_("JGLOBAL_PASSWORD") ?>">
        </div>
	<?php endif; ?>
    <div class="d-flex justify-content-center">
        <button type="submit" class="btn btn-primary btn-sm"><?php echo Text::_("COM_PLUGNMEET_JOIN") ?></button>
    </div>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="application/javascript">
    window.addEventListener("load", () => {
        const form = document.getElementById("pnmJoinForm");
        const status = document.getElementById("status");
        const errorMsg = document.getElementById("errorMsg");

        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            status.hidden = false;
            errorMsg.innerHTML = "";

            try {
                const body = new FormData(e.target);
                const req = await fetch(e.target.action, {
                    method: 'POST',
                    body: body,
                });

                const res = await req.json();
                if (res.status && res.url) {
                    const windowOpen = window.open(res.url, "_blank");
                    if (!windowOpen) {
                        setTimeout(() => {
                            // check, if still not opened
                            if (!windowOpen) {
                                status.hidden = false;
                                window.location.href = res.url;
                            }
                        }, 5000);
                    }
                } else {
                    errorMsg.innerHTML = res.msg;
                }
            } catch (e) {
                errorMsg.innerHTML = e.toString();
            }
            status.hidden = true;
        });
    });
</script>
