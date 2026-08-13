<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<form action="<?php echo Route::_('index.php?option=com_plugnmeet&view=artifacts') ?>"
      method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <div class="plugnmeet-room-selector">
                    <select name="roomId" id="plugnmeet-artifacts-room" class="form-select">
                        <option value=""><?php echo Text::_('COM_PLUGNMEET_ARTIFACTS_SELECT_ROOM'); ?></option>
                        <?php foreach ($this->rooms as $room) : ?>
                            <option value="<?php echo $this->escape($room->room_id); ?>" <?php echo (!empty($this->selectedRoomId) && $this->selectedRoomId === $room->room_id) ? 'selected="selected"' : ''; ?>>
                                <?php echo $this->escape($room->room_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="btn btn-primary"
                            id="plugnmeet-show-artifacts"><?php echo Text::_('COM_PLUGNMEET_ARTIFACTS_SHOW'); ?></button>
                </div>

                <div class="clearfix"></div>

                <table class="table table-striped" id="artifactLists">
                    <thead>
                    <tr>
                        <th class="text-center">
                            <?php echo Text::_('COM_PLUGNMEET_ARTIFACTS_ARTIFACT_ID'); ?>
                        </th>
                        <th class="text-center">
                            <?php echo Text::_('COM_PLUGNMEET_ARTIFACTS_TYPE'); ?>
                        </th>
                        <th class="text-center">
                            <?php echo Text::_('COM_PLUGNMEET_ARTIFACTS_CREATED'); ?>
                        </th>
                        <th class="w-3 text-center">
                        </th>
                    </tr>
                    </thead>
                    <tbody id="artifactListsBody" class="text-center">
                    </tbody>
                </table>

                <div class="plugnmeet-pagination-wrapper">
                    <div id="plugnmeet-artifacts-info"></div>
                    <div id="artifactListsFooter" style="display: none;">
                        <ul class="pagination justify-content-end">
                            <li class="page-item">
                                <button type="button" class="btn btn-sm btn-primary"
                                        id="artifactsBackward"><?php echo Text::_('JPREV'); ?></button>
                            </li>
                            <li class="page-item">
                                <button type="button" class="btn btn-sm btn-primary"
                                        id="artifactsForward"><?php echo Text::_('JNEXT'); ?></button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
