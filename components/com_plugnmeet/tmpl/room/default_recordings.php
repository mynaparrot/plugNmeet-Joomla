<?php
/**
 * @since       2.0.0
 * @package     com_plugnmeet
 * @author      Jibon L. Costa <jibon@mynaparrot.com>
 * @copyright   Copyright (C) MynaParrot SL. All Rights Reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$user      = Factory::getApplication()->getIdentity();
$assetName = sprintf("com_plugnmeet.room.%d", $this->item->id);
?>
<hr/>
<div class="row">
    <h6 class="card-title mb-4"><?php echo Text::_("COM_PLUGNMEET_RECORDING_TITLE"); ?></h6>
    <table class="table table-hover">
        <thead class="table-light">
        <tr class="text-center">
            <th scope="col"><?php echo Text::_("COM_PLUGNMEET_RECORDING_METADATA_TITLE"); ?></th>
            <th scope="col"><?php echo Text::_("COM_PLUGNMEET_RECORDING_DATE"); ?></th>
            <th scope="col"><?php echo Text::_("COM_PLUGNMEET_RECORDING_MEETING_DATE"); ?></th>
            <th scope="col"><?php echo Text::_("COM_PLUGNMEET_RECORDING_SIZE"); ?></th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody id="recordingListsBody" class="text-center">
        </tbody>
    </table>
    <ul class="pagination justify-content-end" style="display: none">
        <li class="page-item mx-3">
            <button class="btn btn-sm btn-primary" id="backward"><?php echo Text::_("Pre"); ?></button>
        </li>
        <li class="page-item">
            <button class="btn btn-sm btn-primary" id="forward"><?php echo Text::_("Next"); ?></button>
        </li>
    </ul>
</div>

<script type="application/javascript">
    const CAN_PLAY = <?php echo $user->authorise("recording.play", $assetName) ? 1 : 0 ?>;
    const CAN_DOWNLOAD = <?php echo $user->authorise("recording.download", $assetName) ? 1 : 0 ?>;
    const CAN_DELETE = <?php echo $user->authorise("recording.delete", $assetName) ? 1 : 0 ?>;
    const id = <?php echo $this->item->id; ?>;
    let isShowingPagination = false,
        totalRecordings = 0,
        currentPage = 1,
        limitPerPage = 10,
        showPre = false,
        showNext = true;

    window.addEventListener("load", () => {
        document.addEventListener('click', function (e) {
            if (e.target.id === 'backward') {
                e.preventDefault();
                if (!showPre) {
                    return;
                }
                currentPage--;
                paginate(currentPage);
            } else if (e.target.id === 'forward') {
                e.preventDefault();
                if (!showNext) {
                    return;
                }
                currentPage++;
                paginate(currentPage);
            }
        });
        fetchRecordings();
    });

    const fetchRecordings = async (from = 0, limitPerPage = 10) => {
        const formData = new FormData();

        formData.append(Joomla.getOptions("csrf.token", ""), 1);
        formData.append("option", "com_plugnmeet");
        formData.append("view", "room");
        formData.append("task", "room.getRecordings");
        formData.append("id", id);
        formData.append('from', from);
        formData.append('limit', limitPerPage);
        formData.append('order_by', 'DESC');

        const data = await sendRequest(formData);
        if (!data) {
            return;
        }

        if (!data.status) {
            showMessage(data.msg);
            return;
        }
        const result = JSON.parse(data.result);
        const recordings = result.recordingsList;
        if (!recordings) {
            showMessage('no recordings found');
            return;
        }
        totalRecordings = result.totalRecordings;
        if (
            totalRecordings > limitPerPage &&
            !isShowingPagination
        ) {
            showPagination();
            isShowingPagination = true;
        }
        displayRecordings(recordings);
    }

    const downloadRecording = async (e) => {
        e.preventDefault();
        const recordId = e.target.attributes.getNamedItem('data-recording').value;

        const formData = new FormData();
        formData.append(Joomla.getOptions("csrf.token", ""), 1);
        formData.append("option", "com_plugnmeet");
        formData.append("view", "room");
        formData.append("task", "room.getRecordingLink");
        formData.append("id", id);
        formData.append('recordingId', recordId);
        formData.append('access', 'recording.download');

        const res = await sendRequest(formData);
        if (!res) {
            return;
        }

        if (res.status && res.url) {
            window.open(res.url, "_blank");
        } else {
            alert(res.msg);
        }
    }

    const playRecording = async (e, i) => {
        e.preventDefault();
        const recordId = e.target.attributes.getNamedItem('data-recording').value;

        const formData = new FormData();
        formData.append(Joomla.getOptions("csrf.token", ""), 1);
        formData.append("option", "com_plugnmeet");
        formData.append("view", "room");
        formData.append("task", "room.getRecordingLink");
        formData.append("id", id);
        formData.append('recordingId', recordId);
        formData.append('access', 'recording.play');

        const res = await sendRequest(formData);
        if (!res) {
            return;
        }

        if (res.status && res.url) {
            displayPlayerModal(res.url);
        } else {
            alert(res.msg);
        }
    }

    const deleteRecording = async (e) => {
        e.preventDefault();

        if (confirm('<?php echo Text::_("COM_PLUGNMEET_DELETE_MESSAGE"); ?>') !== true) {
            return;
        }

        const recordId = e.target.attributes.getNamedItem('data-recording').value;
        const formData = new FormData();
        formData.append(Joomla.getOptions("csrf.token", ""), 1);
        formData.append("option", "com_plugnmeet");
        formData.append("view", "room");
        formData.append("task", "room.deleteRecording");
        formData.append("id", id);
        formData.append('recordingId', recordId);

        const res = await sendRequest(formData);
        if (!res) {
            return;
        }

        if (res.status) {
            alert(res.msg);
            document.getElementById(recordId).remove();
        } else {
            alert(res.msg);
        }
    }

    const displayRecordings = (recordings) => {
        let html = '';
        for (let i = 0; i < recordings.length; i++) {
            const recording = recordings[i];
            let title = "<?php echo $this->item->title;  ?>";
            if (typeof recording.metadata !== "undefined" && recording.metadata.title) {
                title = recording.metadata.title;
            }
            html += '<tr class="table-item" id="' + recording.recordId + '">';
            html += '<td class="meeting-title">' + title + '</td>';
            html +=
                '<td class="recording-date" id="r_creation_' + i + '">' +
                new Date(recording.creationTime * 1e3).toLocaleString() +
                '</td>';
            html +=
                '<td class="meeting-date">' +
                new Date(recording.roomCreationTime * 1e3).toLocaleString() +
                '</td>';
            html += '<td class="file-size">' + recording.fileSize + '</td>';

            html += '<td><div class="action">';
            if (CAN_PLAY) {
                html +=
                    '<button type="button" class="btn btn-primary btn-sm mx-1" data-recording="' +
                    recording.recordId +
                    '" onclick="playRecording(event, ' + i + ')"><?php echo Text::_("Play"); ?></button>';
            }

            if (CAN_DOWNLOAD) {
                html +=
                    '<button type="button" class="btn btn-success btn-sm mx-2" data-recording="' +
                    recording.recordId +
                    '" onclick="downloadRecording(event)"><?php echo Text::_("Download"); ?></button>';
            }

            if (CAN_DELETE) {
                html +=
                    '<button type="button" class="btn btn-danger btn-sm" data-recording="' +
                    recording.recordId +
                    '" onclick="deleteRecording(event)"><?php echo Text::_("Delete"); ?></button>';
            }
            html += '</div></td>';

            html += '</tr>';
        }

        document.getElementById('recordingListsBody').innerHTML = html;
    }

    const showPagination = () => {
        currentPage = 1;
        document.querySelector('.pagination').style.display = '';
        paginate(currentPage);
    }

    const paginate = (currentPage) => {
        document.getElementById('recordingListsBody').innerHTML = '';
        const from = (currentPage - 1) * limitPerPage;

        if (currentPage === 1) {
            showPre = false;
            document.getElementById('backward').setAttribute('disabled', 'disabled');
        } else {
            showPre = true;
            document.getElementById('backward').removeAttribute('disabled');
        }

        if (currentPage >= totalRecordings / limitPerPage) {
            showNext = false;
            document.getElementById('forward').setAttribute('disabled', 'disabled');
        } else {
            showNext = true;
            document.getElementById('forward').removeAttribute('disabled');
        }

        fetchRecordings(from, limitPerPage);
    }

    const showMessage = (msg) => {
        document.getElementById('recordingListsBody').innerHTML = "<tr><td colspan='4' class='text-center'>" + msg + "</td></tr>";
    }

    const sendRequest = async (formData) => {
        const res = await fetch("<?php echo Uri::base(); ?>", {
            method: 'POST',
            body: formData
        })

        if (!res.ok) {
            console.error(res.status, res.statusText);
            alert(res.statusText);
            return null;
        }

        try {
            return await res.json();
        } catch (e) {
            console.error(e);
            alert(e);
        }

        return null;
    }
</script>
