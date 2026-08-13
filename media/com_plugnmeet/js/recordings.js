/**
 * @version     CVS: 1.0.0
 * @package     com_plugnmeet
 * @copyright   2024 Jibon
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Jibon <jiboncosta57@gmail.com>
 */
window.Joomla = window.Joomla || {};

(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const opts = Joomla.getOptions('plugnmeet.recordings', {});
        const token = Joomla.getOptions('csrf.token', '');

        let roomId = '',
            totalRecordings = 0,
            currentPage = 1,
            limitPerPage = 20,
            selectedRecordings = [],
            showPre = false,
            showNext = true;

        const roomSelect = document.getElementById('plugnmeet-recordings-room');
        const showButton = document.getElementById('plugnmeet-show-recordings');
        const mergeButton = document.getElementById('plugnmeet-merge-recordings');
        const recordingsBody = document.getElementById('recordingListsBody');
        const recordingsInfo = document.getElementById('plugnmeet-recordings-info');
        const recordingsFooter = document.getElementById('recordingListsFooter');
        const backwardButton = document.getElementById('recordingsBackward');
        const forwardButton = document.getElementById('recordingsForward');
        const selectAll = document.getElementById('cb-select-all-1');
        const mergeModal = document.getElementById('plugnmeet-merge-modal');
        const mergeList = document.getElementById('plugnmeet-merge-list');
        const cancelMerge = document.getElementById('plugnmeet-cancel-merge');
        const confirmMerge = document.getElementById('plugnmeet-confirm-merge');

        const sendRequest = async (task, data) => {
            const formData = new FormData();

            formData.append(token, 1);
            formData.append('option', 'com_plugnmeet');
            formData.append('view', 'recordings');
            formData.append('task', task);

            Object.keys(data).forEach((key) => {
                const value = data[key];
                if (Array.isArray(value)) {
                    value.forEach((item) => formData.append(key + '[]', item));
                } else {
                    formData.append(key, value);
                }
            });

            try {
                const res = await fetch(opts.baseUrl, {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) {
                    console.error(res.status, res.statusText);
                    alert(res.statusText);
                    return null;
                }

                return await res.json();
            } catch (e) {
                console.error(e);
                alert(e);
            }

            return null;
        };

        const showMessage = (msg) => {
            recordingsBody.innerHTML =
                '<tr><td colspan="6" class="text-center">' + msg + '</td></tr>';
        };

        const updateRecordingsInfo = (totalPages) => {
            let infoText = opts.i18n.totalRecordings + ': ' + totalRecordings;
            if (totalPages > 1) {
                infoText += ' | ' + opts.i18n.page + ': ' + currentPage + '/' + totalPages;
            }
            recordingsInfo.innerHTML = infoText;
            recordingsInfo.style.display = '';
        };

        const updatePaginationButtons = (page, totalPages) => {
            if (page <= 1) {
                showPre = false;
                backwardButton.setAttribute('disabled', 'disabled');
            } else {
                showPre = true;
                backwardButton.removeAttribute('disabled');
            }

            if (page >= totalPages) {
                showNext = false;
                forwardButton.setAttribute('disabled', 'disabled');
            } else {
                showNext = true;
                forwardButton.removeAttribute('disabled');
            }
        };

        const fetchRecordings = async (from, limit) => {
            recordingsBody.innerHTML = '';

            const res = await sendRequest('recordings.getRecordings', {
                roomId,
                from: from,
                limit: limit,
                order_by: 'DESC'
            });

            if (!res) {
                return;
            }

            if (!res.status) {
                showMessage(res.msg);
                recordingsInfo.style.display = 'none';
                return;
            }

            const result = JSON.parse(res.result);
            const recordings = result.recordingsList;
            totalRecordings = result.totalRecordings;

            if (!recordings || recordings.length === 0) {
                showMessage(opts.i18n.noRecordings);
                recordingsInfo.style.display = 'none';
                return;
            }

            let html = '';
            recordings.forEach((recording) => {
                html += '<tr>';
                html += '<td class="text-center"><input type="checkbox" class="form-check-input recording-checkbox" name="recordings[]" value="' + recording.recordId + '"></td>';
                html += '<td>' + recording.recordId + '</td>';
                html += '<td>' + new Date(recording.creationTime * 1e3).toLocaleString() + '</td>';
                html += '<td>' + new Date(recording.roomCreationTime * 1e3).toLocaleString() + '</td>';
                html += '<td>' + parseFloat(recording.fileSize).toFixed(2) + '</td>';
                html += '<td><div class="plugnmeet-action-buttons">';
                html += '<button type="button" class="btn btn-success btn-sm plugnmeet-download-recording" data-recording="' + recording.recordId + '">' + opts.i18n.download + '</button>';
                if (opts.canDelete) {
                    html += '<button type="button" class="btn btn-danger btn-sm plugnmeet-delete-recording" data-recording="' + recording.recordId + '">' + opts.i18n.delete + '</button>';
                }
                html += '</div></td>';
                html += '</tr>';
            });

            recordingsBody.innerHTML = html;

            // Check if pagination is required
            const totalPages = Math.ceil(totalRecordings / limitPerPage);
            if (totalPages > 1) {
                recordingsFooter.style.display = '';
                updatePaginationButtons(currentPage, totalPages);
                updateRecordingsInfo(totalPages);
            } else {
                recordingsFooter.style.display = 'none';
                recordingsInfo.style.display = 'none';
            }
        };

        const paginate = (page) => {
            currentPage = page;
            const from = (page - 1) * limitPerPage;
            const totalPages = Math.ceil(totalRecordings / limitPerPage);

            updatePaginationButtons(page, totalPages);
            fetchRecordings(from, limitPerPage);
            updateRecordingsInfo(totalPages);
        };

        const initLoadRecordings = () => {
            roomId = roomSelect.value;
            if (!roomId) {
                showMessage(opts.i18n.selectRoomRequired);
                recordingsInfo.style.display = 'none';
                recordingsFooter.style.display = 'none';
                return;
            }

            selectedRecordings = [];
            mergeButton.style.display = 'none';
            currentPage = 1;
            recordingsFooter.style.display = 'none';
            recordingsInfo.style.display = 'none';
            fetchRecordings(0, limitPerPage);
        };

        showButton.addEventListener('click', (e) => {
            e.preventDefault();
            initLoadRecordings();
        });

        roomSelect.addEventListener('change', () => {
            selectedRecordings = [];
            mergeButton.style.display = 'none';
        });

        if (opts.initialRoomId) {
            roomSelect.value = opts.initialRoomId;
            initLoadRecordings();
        }

        recordingsBody.addEventListener('click', (e) => {
            const target = e.target;

            if (target.classList.contains('plugnmeet-download-recording')) {
                const recordingId = target.getAttribute('data-recording');
                if (!recordingId) {
                    return;
                }

                sendRequest('recordings.downloadRecording', {recordingId}).then((res) => {
                    if (!res) {
                        return;
                    }

                    if (res.status && res.url) {
                        window.open(res.url, '_blank');
                    } else {
                        alert(res.msg);
                    }
                });
            }

            if (target.classList.contains('plugnmeet-delete-recording')) {
                const recordingId = target.getAttribute('data-recording');
                if (!recordingId) {
                    return;
                }

                if (!confirm(opts.i18n.confirmDelete)) {
                    return;
                }

                sendRequest('recordings.deleteRecording', {recordingId}).then((res) => {
                    if (!res) {
                        return;
                    }

                    if (res.status) {
                        target.closest('tr').remove();
                    } else {
                        alert(res.msg);
                    }
                });
            }
        });

        recordingsBody.addEventListener('change', (e) => {
            const target = e.target;

            if (!target.classList.contains('recording-checkbox')) {
                return;
            }

            const recordId = target.value;
            if (target.checked) {
                if (!selectedRecordings.includes(recordId)) {
                    selectedRecordings.push(recordId);
                }
            } else {
                selectedRecordings = selectedRecordings.filter((id) => id !== recordId);
            }

            mergeButton.style.display = selectedRecordings.length > 1 ? '' : 'none';
        });

        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.recording-checkbox').forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
                checkbox.dispatchEvent(new Event('change'));
            });
        });

        backwardButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (!showPre) {
                return;
            }
            paginate(currentPage - 1);
        });

        forwardButton.addEventListener('click', (e) => {
            e.preventDefault();
            if (!showNext) {
                return;
            }
            paginate(currentPage + 1);
        });

        mergeButton.addEventListener('click', () => {
            let listHtml = '';
            selectedRecordings.forEach((recordId) => {
                listHtml += '<li>' + recordId + '</li>';
            });
            mergeList.innerHTML = listHtml;

            const msgDiv = document.getElementById('plugnmeet-merge-msg');
            msgDiv.style.display = 'none';
            msgDiv.classList.remove('alert', 'alert-success', 'alert-danger');
            confirmMerge.removeAttribute('disabled');

            const modal = bootstrap.Modal.getOrCreateInstance(mergeModal);
            modal.show();
        });

        cancelMerge.addEventListener('click', () => {
            const modal = bootstrap.Modal.getInstance(mergeModal);
            if (modal) {
                modal.hide();
            }
        });

        confirmMerge.addEventListener('click', () => {
            confirmMerge.setAttribute('disabled', 'disabled');

            sendRequest('recordings.mergeRecordings', {
                recordings: selectedRecordings,
                roomId
            }).then((res) => {
                const msgDiv = document.getElementById('plugnmeet-merge-msg');

                if (!res) {
                    confirmMerge.removeAttribute('disabled');
                    return;
                }

                msgDiv.textContent = res.msg;
                msgDiv.style.display = '';

                if (res.status) {
                    msgDiv.classList.add('alert', 'alert-success');
                    msgDiv.classList.remove('alert-danger');
                    alert(res.msg);
                    const modal = bootstrap.Modal.getInstance(mergeModal);
                    if (modal) {
                        modal.hide();
                    }
                    selectedRecordings = [];
                    mergeButton.style.display = 'none';
                    initLoadRecordings();
                } else {
                    msgDiv.classList.add('alert', 'alert-danger');
                    msgDiv.classList.remove('alert-success');
                    confirmMerge.removeAttribute('disabled');
                }
            });
        });
    });
})();
