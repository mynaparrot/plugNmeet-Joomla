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
        const opts = Joomla.getOptions('plugnmeet.artifacts', {});
        const token = Joomla.getOptions('csrf.token', '');

        const sendRequest = async (task, data) => {
            const formData = new FormData();

            formData.append(token, 1);
            formData.append('option', 'com_plugnmeet');
            formData.append('view', 'artifacts');
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

        // List page
        const roomSelect = document.getElementById('plugnmeet-artifacts-room');

        if (roomSelect) {
            let roomId = '',
                totalArtifacts = 0,
                currentPage = 1,
                limitPerPage = 20,
                showPre = false,
                showNext = true;

            const showButton = document.getElementById('plugnmeet-show-artifacts');
            const artifactsBody = document.getElementById('artifactListsBody');
            const artifactsInfo = document.getElementById('plugnmeet-artifacts-info');
            const artifactsFooter = document.getElementById('artifactListsFooter');
            const backwardButton = document.getElementById('artifactsBackward');
            const forwardButton = document.getElementById('artifactsForward');

            const showMessage = (msg) => {
                artifactsBody.innerHTML =
                    '<tr><td colspan="4" class="text-center">' + msg + '</td></tr>';
            };

            const updateArtifactsInfo = (totalPages) => {
                let infoText = opts.i18n.totalArtifacts + ': ' + totalArtifacts;
                if (totalPages > 1) {
                    infoText += ' | ' + opts.i18n.page + ': ' + currentPage + '/' + totalPages;
                }
                artifactsInfo.innerHTML = infoText;
                artifactsInfo.style.display = '';
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

            const fetchArtifacts = async (from, limit) => {
                artifactsBody.innerHTML = '';

                const res = await sendRequest('artifacts.getArtifacts', {
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
                    artifactsInfo.style.display = 'none';
                    return;
                }

                const result = JSON.parse(res.result);
                const artifacts = result.artifactsList;
                totalArtifacts = result.totalArtifacts;

                if (!artifacts || artifacts.length === 0) {
                    showMessage(opts.i18n.noArtifacts);
                    artifactsInfo.style.display = 'none';
                    return;
                }

                let html = '';
                artifacts.forEach((artifact) => {
                    html += '<tr>';
                    html += '<td>' + artifact.artifact_id + '</td>';
                    html += '<td>' + artifact.type + '</td>';
                    html += '<td>' + artifact.created + '</td>';
                    html += '<td><div class="plugnmeet-action-buttons">';
                    html += '<a href="' + artifact.view_url + '" class="btn btn-primary btn-sm">' + opts.i18n.view + '</a>';
                    html += '</div></td>';
                    html += '</tr>';
                });

                artifactsBody.innerHTML = html;

                // Check if pagination is required
                const totalPages = Math.ceil(totalArtifacts / limitPerPage);
                if (totalPages > 1) {
                    artifactsFooter.style.display = '';
                    updatePaginationButtons(currentPage, totalPages);
                    updateArtifactsInfo(totalPages);
                } else {
                    artifactsFooter.style.display = 'none';
                    artifactsInfo.style.display = 'none';
                }
            };

            const paginate = (page) => {
                currentPage = page;
                const from = (page - 1) * limitPerPage;
                const totalPages = Math.ceil(totalArtifacts / limitPerPage);

                updatePaginationButtons(page, totalPages);
                fetchArtifacts(from, limitPerPage);
                updateArtifactsInfo(totalPages);
            };

            const initLoadArtifacts = () => {
                roomId = roomSelect.value;
                if (!roomId) {
                    showMessage(opts.i18n.selectRoomRequired);
                    artifactsInfo.style.display = 'none';
                    artifactsFooter.style.display = 'none';
                    return;
                }

                artifactsFooter.style.display = 'none';
                artifactsInfo.style.display = 'none';
                fetchArtifacts((currentPage - 1) * limitPerPage, limitPerPage);
            };

            showButton.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = 1;
                initLoadArtifacts();
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

            // Restore the previously selected room and page when returning from the details view.
            if (opts.initialRoomId) {
                roomSelect.value = opts.initialRoomId;
                currentPage = opts.initialPage || 1;
                initLoadArtifacts();
            }
        }

        // Detail page
        const downloadButton = document.querySelector('.plugnmeet-download-artifact');
        const deleteButton = document.querySelector('.plugnmeet-delete-artifact');

        if (downloadButton || deleteButton) {
            if (downloadButton) {
                downloadButton.addEventListener('click', (e) => {
                    e.preventDefault();

                    const artifactId = downloadButton.getAttribute('data-artifact-id');
                    if (!artifactId) {
                        return;
                    }

                    sendRequest('artifacts.downloadArtifact', {artifact_id: artifactId}).then((res) => {
                        if (!res) {
                            return;
                        }

                        if (res.status && res.url) {
                            window.location.href = res.url;
                        } else {
                            alert(res.msg);
                        }
                    });
                });
            }

            if (deleteButton) {
                deleteButton.addEventListener('click', (e) => {
                    e.preventDefault();

                    const artifactId = deleteButton.getAttribute('data-artifact-id');
                    if (!artifactId) {
                        return;
                    }

                    if (!confirm(opts.i18n.confirmDelete)) {
                        return;
                    }

                    sendRequest('artifacts.deleteArtifact', {artifact_id: artifactId}).then((res) => {
                        if (!res) {
                            return;
                        }

                        if (res.status) {
                            window.location.href = opts.listUrl;
                        } else {
                            alert(res.msg);
                        }
                    });
                });
            }
        }
    });
})();
