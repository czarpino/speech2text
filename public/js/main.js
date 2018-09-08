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
                base64Promise.finally(function () {

                    // Show button
                    $('.js-file-processing').hide();
                    $('.js-file-upload').show();
                });
                startUpload(base64Promise, fileInput.files[0].name);
            }
        });
    });

    function startUpload(base64Promise, filename) {
        base64Promise.then(function (audioData) {
            $.post('/api/audio/upload').done(function (data) {
                let $shadow = insertShadowRow(data.id, filename);

                let chunkSize = 524288;
                let dataLength = audioData.length;
                let doneCount = 0;

                for (let i = 0; i * chunkSize < dataLength; i ++) {
                    let startIdx = i * chunkSize;

                    $.post('/api/audio/chunk', {
                        'upload_id': data.id,
                        'audio_data': audioData.substring(startIdx, startIdx + chunkSize),
                        'order': i + 1
                    }).done(function (data) {
                        doneCount ++;
                        if (dataLength <= doneCount * chunkSize) {

                            // All chunks have been sent. Send merge request.
                            $.post('/api/audio/merge', {
                                'upload_id': data.id
                            }).done(function () {

                                insertNewAudio($shadow, data.id);

                                // TODO insert new row or reload
                                console.log('Chunks have merged. Page may be reloaded.');
                            });
                        }
                    });
                }
            });
        });
    }

    function insertShadowRow(uploadId, filename) {
        let $shadow = $(`
            <tr>
                <td><input type="checkbox" class="checkthis" disabled/></td>
                <td>${filename.substring(0, 8)}...</td>
                <td>-</td>
                <td>Uploading...</td>
                <td>-</td>
            </tr>
        `);

        $('.js-upload-list').append($shadow);

        return $shadow;
    }

    function insertNewAudio($shadow, uploadId) {
        // TODO fetch by id
        return $shadow.replaceWith(`
            <tr>
                <td><input type="checkbox" class="checkthis"/></td>
                <td>Fooo</td>
                <td>Today</td>
                <td>Transcribing</td>
                <td>
                    <button class="btn btn-primary">Play</button>
                    <button class="btn btn-link">Transcription</button>
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
