(function ($) {

    'use strict';

    $(document).on(
        'click',
        '#formnova-test-captcha',
        function () {

            const button = $(this);

            button.prop('disabled', true);

            $.post(
                FormNova.ajax_url,
                {
                    action: 'formnova_test_captcha',
                    nonce: FormNova.nonce
                },
                function (response) {

                    alert(response.data.message);

                    button.prop('disabled', false);

                }
            );

        }
    );

})(jQuery);