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
    <form action="<?php echo Route::_('index.php?option=com_plugnmeet&view=recordings') ?>" method="post"
          name="adminForm" id="adminForm">
        <div class="row">
            <div class="col-md-12">
                <div id="j-main-container" class="j-main-container">
                    <div class="plugnmeet-room-selector">
                        <select name="roomId" id="plugnmeet-recordings-room" class="form-select">
                            <option value=""><?php echo Text::_('COM_PLUGNMEET_RECORDINGS_SELECT_ROOM'); ?></option>
                            <?php foreach ($this->rooms as $room) : ?>
                                <option value="<?php echo $this->escape($room->room_id); ?>" <?php echo (!empty($this->selectedRoomId) && $this->selectedRoomId === $room->room_id) ? 'selected="selected"' : ''; ?>>
                                    <?php echo $this->escape($room->room_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-primary"
                                id="plugnmeet-show-recordings"><?php echo Text::_('COM_PLUGNMEET_RECORDINGS_SHOW'); ?></button>
                        <button type="button" class="btn btn-primary" id="plugnmeet-merge-recordings"
                                style="display: none;"><?php echo Text::_('COM_PLUGNMEET_RECORDINGS_MERGE'); ?></button>
                    </div>

                    <div class="clearfix"></div>

                    <table class="table table-striped" id="recordingLists">
                        <thead>
                        <tr>
                            <th class="w-1 text-center">
                                <input type="checkbox" autocomplete="off" class="form-check-input" id="cb-select-all-1"
                                       name="checkall-toggle" value=""
                                       title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>"/>
                            </th>
                            <th class="text-center">
                                <?php echo Text::_('COM_PLUGNMEET_RECORDINGS_RECORD_ID'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo Text::_('COM_PLUGNMEET_RECORDINGS_RECORDING_DATE'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo Text::_('COM_PLUGNMEET_RECORDINGS_MEETING_DATE'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo Text::_('COM_PLUGNMEET_RECORDINGS_FILE_SIZE'); ?>
                            </th>
                            <th class="w-3 text-center">
                            </th>
                        </tr>
                        </thead>
                        <tbody id="recordingListsBody" class="text-center">
                        </tbody>
                    </table>

                    <div class="plugnmeet-pagination-wrapper">
                        <div id="plugnmeet-recordings-info"></div>
                        <div id="recordingListsFooter" style="display: none;">
                            <ul class="pagination justify-content-end">
                                <li class="page-item">
                                    <button type="button" class="btn btn-sm btn-primary"
                                            id="recordingsBackward"><?php echo Text::_('JPREV'); ?></button>
                                </li>
                                <li class="page-item">
                                    <button type="button" class="btn btn-sm btn-primary"
                                            id="recordingsForward"><?php echo Text::_('JNEXT'); ?></button>
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

<?php
$mergeBody = '<p>' . Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_MERGE_MSG') . '</p>'
        . '<ul id="plugnmeet-merge-list"></ul>'
        . '<div id="plugnmeet-merge-msg" style="display: none; margin-top: 10px;"></div>';

$mergeFooter = '<button type="button" class="btn btn-secondary" id="plugnmeet-cancel-merge">' . Text::_('COM_PLUGNMEET_RECORDINGS_CANCEL') . '</button>'
        . '<button type="button" class="btn btn-primary" id="plugnmeet-confirm-merge">' . Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_MERGE') . '</button>';

echo HTMLHelper::_(
        'bootstrap.renderModal',
        'plugnmeet-merge-modal',
        array(
                'title'    => Text::_('COM_PLUGNMEET_RECORDINGS_CONFIRM_MERGE_TITLE'),
                'footer'   => $mergeFooter,
                'backdrop' => 'static'
        ),
        $mergeBody
);
