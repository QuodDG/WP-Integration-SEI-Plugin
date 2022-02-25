(function ($) {
    let qdo_isei_rd_lougout_btn = $('#i__qdo-isei-rd-lougout-btn');
    let hk = qdo_isei_rd_lougout_btn.attr('data-hk');

    qdo_isei_rd_lougout_btn.on('click', function (e) {
        e.preventDefault();

        isei_request({
            url: `${location.origin}/wp-json/isei/v2/logout-rdstation/${hk}`,
            callback: response_isei_logout_rdstation,
            btn: qdo_isei_rd_lougout_btn,
        });
    });

    console.log('QdoIntegrationSei initialized');

    function response_isei_logout_rdstation(response) {
        if (response.error) {
            isei_show_message(response.message, 'error');
            return;
        }
        
        location.reload();
    }

    function isei_show_message(text, type) {
        var message = $('#i__qdo-isei-content-message');
        if (type === 'success') {
            message.addClass('qdo-isei-content-message-success');
        } else if (type === 'error') {
            message.addClass('qdo-isei-content-message-error');
        }
        message.show().html(text);
    }

    function isei_clear_message() {
        var message = $('#i__qdo-isei-content-message');
        message
                .hide()
                .removeClass('qdo-isei-content-message-success')
                .removeClass('qdo-isei-content-message-error')
                .html('');
    }

    /**
     * 
     * @param {type} options [url, data, method, btn, contentType, processData, callback]
     * @returns {undefined}
     */
    function isei_request(options) {
        let btn_label = '';
        if (options.btn !== undefined) {
            btn_label = $(options.btn).text();
            $(options.btn).prop('disabled', true).text('...');
        }
        let ajax_options = {
            data: options.data,
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', qdoIseiSettings.nonce);
            },
            error: function (jqXHR, textStatus) {
                //console.log(jqXHR);
                isei_show_message('Não foi possível completar a requisição.', 'error');
                //alert('Não foi possível completar a requisição.');
                if (options.btn !== undefined) {
                    $(options.btn).prop('disabled', false).text(btn_label);
                }
            },
            method: options.method === undefined ? 'GET' : options.method,
            success: function (response) {
                if (options.btn !== undefined) {
                    $(options.btn).prop('disabled', false).text(btn_label);
                }
                options.callback(response);
            }
        };
        if (options.contentType !== undefined) {
            ajax_options.contentType = options.contentType;
        }
        if (options.processData !== undefined) {
            ajax_options.processData = options.processData;
        }
        isei_clear_message();
        $.ajax(options.url, ajax_options);
    }
})(jQuery);