<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

HTMLHelper::_('bootstrap.modal', '.modal', []);
?>
<div class="modal fade" id="playerModal" tabindex="-1" aria-labelledby="playerLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo Text::_("COM_PLUGNMEET_RECORDING_PLAYER_MODAL_TITLE") ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <video class="w-100" controls controlsList="nodownload" id="playbackVideo">
                </video>
            </div>
        </div>
    </div>
</div>

<script>
    const displayPlayerModal = (playBackUrl) => {
        const video = document.getElementById("playbackVideo");
        video.src = playBackUrl;

        const modal = document.getElementById('playerModal');
        modal.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        modal.addEventListener('hidden.bs.modal', function () {
            video.src = "";
        });

        const playerModal = new bootstrap.Modal(modal);
        playerModal.show();
    }
</script>
