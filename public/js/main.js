(function ($) {
    $(function () {

        // Open file selector
        $('.js-file-upload').off('click').on('click', function (e) {
            $('.js-file-select').trigger('click');
        });

        // Initiate upload
        $(".js-file-select").change(function (e) {
            let fileInput = this;
            if (fileInput.files) {

                // Show processing
                $('.js-file-processing').show();
                $('.js-file-upload').hide();

                let base64Promise = getBase64(fileInput.files[0]);
                base64Promise.then(function () { fileInput.value = ''; });
                startUpload(base64Promise, fileInput.files[0].name);
            }
        });

        // Select all
        $('.js-audio-header-check').off('click').on('click', function (e) {
            $('.js-audio-row-check').prop('checked', $(this).prop('checked'));
        });

        // Delete
        $('.js-delete-audio').off('click').on('click', function (e) {
            $('.js-audio-row-check:checked').each(function (idx, row) {
                let $audioRow = $(row).closest('tr');

                $audioRow.css('background-color', 'rgba(200,0,0,0.05)');
                $.ajax({
                    url: '/api/audio/' + row.value,
                    type: 'DELETE'
                }).done(function (data) {
                    $audioRow.remove();
                });
            });
        });
    });

    function startUpload(base64Promise, filename) {
        base64Promise.then(function (audioData) {
            let $shadow = insertShadowRow();

            $.post('/api/audio/upload', {
                'filename': filename
            }, function (res, status, xhr) {
                $.get(xhr.getResponseHeader('Location')).done(function (data) {
                    $newRow = createAudioRow(data);
                    $shadow.replaceWith($newRow);
                    $shadow = $newRow;

                    let uploadId = res.id;
                    let chunkSize = 524288; // ~500Kb
                    let dataLength = audioData.length;
                    let doneCount = 0;

                    for (let i = 0; i * chunkSize < dataLength; i ++) {
                        let startIdx = i * chunkSize;

                        $.post('/api/audio/chunk', {
                            'upload_id': uploadId,
                            'order': i + 1,
                            'audio_data': audioData.substring(startIdx, startIdx + chunkSize),
                        }).done(function () {
                            doneCount ++;
                            if (dataLength <= doneCount * chunkSize) {

                                // All chunks have been sent. Send merge request.
                                $.post('/api/audio/merge', {
                                    'upload_id': uploadId
                                }, function (res, status, xhr) {
                                    insertAudioRow(xhr.getResponseHeader('Location'), $shadow);
                                });
                            }
                        });
                    }

                    // Show upload button
                    $('.js-file-processing').hide();
                    $('.js-file-upload').show();
                });
            });
        });
    }

    function insertShadowRow() {
        let $shadowNew = $(`
            <tr>
                <td><input type="checkbox" class="checkthis" disabled/></td>
                <td>---</td>
                <td>---</td>
                <td>---</td>
                <td>---</td>
            </tr>
        `);

        $('.js-upload-list').prepend($shadowNew);
        $('.js-no-row').remove();

        return $shadowNew;
    }

    function insertAudioRow(location, $shadow) {
        $.get(location).done(function (data) {
            $shadow.replaceWith(createAudioRow(data));
        });
    }

    function createAudioRow(audioUpload)
    {
        return $(`
            <tr>
                <td><input type="checkbox" class="checkthis"/></td>
                <td>${audioUpload.filename}</td>
                <td>${audioUpload.uploadDate}</td>
                <td>${audioUpload.statusName}</td>
                <td>
                    <audio controls style="display: ${ audioUpload.status > 0 ? 'block' : 'none' }">
                        <source src="${audioUpload.audioUrl}" type="audio/wav">
                    </audio>
                    <button class="btn btn-link" style="display: ${ audioUpload.status > 1 ? 'block' : 'none' }">Transcription</button>
                </td>
            </tr>
        `);
    }

    function getBase64(file) {
        return new Promise(function (resolve, reject) {
            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = function () {
                resolve(reader.result.split(',')[1]);
            };
            reader.onerror = function (error) {
                reject(error);
            };
        });
    }
})(window.jQuery);
