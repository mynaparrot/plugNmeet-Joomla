<?php
/**
 * @package 	plugNmeet
 * @subpackage	default.php
 * @version		1.0.3
 * @created		4th February, 2022
 * @author		Jibon L. Costa <https://www.plugnmeet.com>
 * @github		<https://github.com/mynaparrot/plugNmeet-Joomla>
 * @copyright	Copyright (C) 2022 mynaparrot. All Rights Reserved
 * @license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
?>
<?php if ($this->canDo->get('recordings.access')): ?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'recordings.back') {
			parent.history.back();
			return false;
		} else {
			var form = document.getElementById('adminForm');
			form.task.value = task;
			form.submit();
		}
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_plugnmeet&view=recordings'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">


<!--[JCBGUI.custom_admin_view.default.1.$$$$]-->
        <?php if (!empty($this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
            <?php else : ?>
            <div id="j-main-container">
                <?php endif; ?>

                <div class="align-self-center" style="margin: auto;width: 50%;">
                    <div class="span5">
                        <label for="roomId">
                            <select name="roomId" id="roomId">
                                <option value="">Select room</option>
                                <?php foreach ($this->items as $item): ?>
                                    <option value="<?php echo $item->room_id; ?>"><?php echo $item->room_title; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="span3">
                        <button class="btn btn-success" id="showRecordings">Show recordings</button>
                    </div>
                </div>

                <table class="table table-striped" id="recordingLists" style="margin-top: 50px">
                    <thead>
                    <tr>
                        <th class="nowrap center">
                            <?php echo JText::_("COM_PLUGNMEET_ID"); ?>
                        </th>
                        <th class="nowrap center">
                            <?php echo JText::_("COM_PLUGNMEET_RECORD_CREATED"); ?>
                        </th>
                        <th class="nowrap center">
                            <?php echo JText::_("COM_PLUGNMEET_MEETING_CREATED"); ?>
                        </th>
                        <th class="nowrap center">
                            <?php echo JText::_("COM_PLUGNMEET_FILE_SIZE_MB"); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody id="recordingListsBody"></tbody>
                    <tfoot id="recordingListsFooter" style="display: none"></tfoot>
                </table>

                <script type="text/javascript">
                    let isShowingPagination = false;
                    const token = '<?php echo JSession::getFormToken() ?>';
                    let roomId = '', totalRecordings = 0, currentPage = 1, limitPerPage = 20;

                    jQuery('document').ready(function ($) {
                        $('#showRecordings').on('click', function (e) {
                            e.preventDefault();
                            roomId = $('#roomId').val();
                            if (!roomId) {
                                return;
                            }
                            const data = {
                                token,
                                from: 0,
                                limit: limitPerPage,
                                order_by: 'DESC',
                                roomId,
                            };

                            fetchRecordings(data);
                            isShowingPagination = false;
                            $('#recordingListsFooter').hide();
                        });

                        $(document).on('click', '.downloadRecording', function (e) {
                            e.preventDefault();
                            const recordingId = $(this).attr('id');
                            if (recordingId) {
                                const url =
                                    '<?php echo JUri::base()?>index.php?option=com_plugnmeet&view=recordings&task=recordings.downloadRecording&token=<?php echo JSession::getFormToken()?>&recordingId=' +
                                    recordingId;
                                window.open(url, '_blank');
                            }
                        });

                        $(document).on('click', '.deleteRecording', function (e) {
                            e.preventDefault();
                            if (confirm('<?php echo JText::_("COM_PLUGNMEET_ARE_YOU_SURE_TO_DELETE"); ?>') !== true) {
                                return;
                            }
                            const recordingId = $(this).attr('id');
                            if (recordingId) {
                                const url =
                                    '<?php echo JUri::base()?>index.php?option=com_plugnmeet&view=recordings&task=recordings.deleteRecording&token=<?php echo JSession::getFormToken()?>&recordingId=' +
                                    recordingId;
                                window.open(url, '_blank');
                            }
                        });

                        function fetchRecordings(data) {
                            $.ajax({
                                url: 'index.php?option=com_plugnmeet&view=recordings&task=recordings.getRecordings',
                                method: 'POST',
                                data,
                                beforeSend: () => {
                                    $('#recordingListsBody').html('');
                                },
                                success: (data) => {
                                    if (!data.status) {
                                        console.log(data.msg);
                                        showMessage(data.msg);
                                        return;
                                    }
                                    const recordings = data.result.recordings_list;
                                    if (!recordings) {
                                        showMessage('<?php echo JText::_("COM_PLUGNMEET_NO_RECORDING_FOUND"); ?>');
                                        return;
                                    }

                                    // check if pagination require
                                    if (
                                        data.result.total_recordings > recordings.length &&
                                        !isShowingPagination
                                    ) {
                                        totalRecordings = data.result.total_recordings;
                                        showPagination();
                                        isShowingPagination = true;
                                    }

                                    let html = '';
                                    for (let i = 0; i < recordings.length; i++) {
                                        const recording = recordings[i];
                                        html += '<tr>';
                                        html += '<td class="center">' + recording.record_id + '</td>';
                                        html +=
                                            '<td class="center">' +
                                            new Date(recording.creation_time * 1e3).toLocaleString() +
                                            '</td>';
                                        html +=
                                            '<td class="center">' +
                                            new Date(recording.room_creation_time * 1e3).toLocaleString() +
                                            '</td>';
                                        html += '<td class="center">' + recording.file_size + '</td>';
                                        html +=
                                            '<td class="center"><button class="btn btn-success downloadRecording" id="' +
                                            recording.record_id +
                                            '"><?php echo JText::_("COM_PLUGNMEET_DOWNLOAD"); ?></button></td>';

                                        html +=
                                            '<td class="center"><button class="btn btn-danger deleteRecording" id="' +
                                            recording.record_id +
                                            '"><?php echo JText::_("COM_PLUGNMEET_DELETE"); ?></button></td>';
                                        html += '</tr>';
                                    }

                                    $('#recordingListsBody').html(html);
                                },
                                error: (jqXHR, textStatus, errorThrown) => {
                                    alert(errorThrown)
                                },
                            });
                        }

                        function showMessage(msg) {
                            const data =
                                '<tr>' +
                                '<td ' +
                                'colspan="6" ' +
                                'class="center">' +
                                msg +
                                '</td>' +
                                '</tr>';
                            $('#recordingListsBody').html(data);
                        }

                        function showPagination() {
                            currentPage = 1;

                            $('#recordingListsFooter').show();
                            let html = '<div class="pagination pagination-toolbar clearfix">';
                            html +=
                                '<nav role="navigation" aria-label="Pagination"><ul class="pagination-list">';

                            html += '<li class="disabled" id="backward"><span>';
                            html +=
                                '<span class="icon-step-backward icon-previous" aria-hidden="true" style="cursor: pointer;"></span>';
                            html += '</span></li>';

                            html += '<li id="forward"><span>';
                            html +=
                                '<span class="icon-step-forward icon-next" aria-hidden="true" style="cursor: pointer;"></span>';
                            html += '</span></li>';

                            html += '</ul></nav>';
                            html += '</div>';

                            $('#recordingListsFooter').html(html);
                        }

                        let showPre = false,
                            showNext = true;

                        $(document).on('click', '#backward', function (e) {
                            e.preventDefault();
                            if (!showPre) {
                                return;
                            }
                            currentPage--;
                            paginate(currentPage);
                        });

                        $(document).on('click', '#forward', function (e) {
                            e.preventDefault();
                            if (!showNext) {
                                return;
                            }
                            currentPage++;
                            paginate(currentPage);
                        });

                        function paginate(currentPage) {
                            const from = (currentPage - 1) * limitPerPage;

                            if (currentPage === 1) {
                                showPre = false;
                                $('#backward').addClass('disabled');
                            } else {
                                showPre = true;
                                $('#backward').removeClass('disabled');
                            }

                            if (currentPage >= totalRecordings / limitPerPage) {
                                showNext = false;
                                $('#forward').addClass('disabled');
                            } else {
                                showNext = true;
                                $('#forward').removeClass('disabled');
                            }

                            const data = {
                                token,
                                from,
                                limit: limitPerPage,
                                order_by: 'DESC',
                                roomId,
                            };
                            fetchRecordings(data);
                        }
                    });

                </script>
            </div><!--[/JCBGUI$$$$]-->

<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
<?php else: ?>
        <h1><?php echo JText::_('COM_PLUGNMEET_NO_ACCESS_GRANTED'); ?></h1>
<?php endif; ?>
