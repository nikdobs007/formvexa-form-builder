(function ($) {

    'use strict';

    $(document).on(
        'click',
        '#formvexa-test-captcha',
        function () {

            const button = $(this);

            button.prop('disabled', true);

            $.post(
                formvexa.ajax_url,
                {
                    action: 'formvexa_test_captcha',
                    nonce: formvexa.nonce
                },
                function (response) {

                    alert(response.data.message);

                    button.prop('disabled', false);

                }
            );

        }
    );

})(jQuery);