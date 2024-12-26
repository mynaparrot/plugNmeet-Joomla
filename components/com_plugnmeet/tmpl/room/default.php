<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

$user      = Factory::getApplication()->getIdentity();
$assetName = sprintf("com_plugnmeet.room.%d", $this->item->id);
?>

<div class="pnm-room mb-3">
	<?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
        </div>
	<?php endif; ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
				<?php echo $this->item->room_title; ?>
            </h5>
        </div>
        <div class="card-body">
            <div class="card-text">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-8">
							<?php echo $this->item->description; ?>
                        </div>
                        <div class="col-sm-4">
							<?php echo $this->loadTemplate("login"); ?>
                        </div>
                    </div>
					<?php
					if ($user->authorise("recording.view", $assetName))
					{
						echo $this->loadTemplate("recordings");
						if ($user->authorise("recording.play", $assetName))
						{
							echo $this->loadTemplate("play_modal");
						}
					}
					?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // check if returned from conference
    const params = new URLSearchParams(document.location.search);
    if (params.has("returned", "true")) {
        // this will only work if link opened with window.open()
        window.close();
    }
</script>
