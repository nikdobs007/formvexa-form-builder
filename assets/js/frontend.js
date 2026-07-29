(function ($) {

    /*
    |--------------------------------------------------------------------------
    | Validation Helpers
    |--------------------------------------------------------------------------
    */

    function showMessage(form, message, type) {

        let box = $(form).find('.formnova-message');

        if (!box.length) {

            box = $('<div class="formnova-message"></div>');

            $(form)
                .find('[type="submit"]')
                .after(box);
        }

        box.removeClass(
            'success error'
        );

        box.addClass(type);

        box.html(message);

    }

    function clearErrors(form) {

        $(form)
            .find('.formnova-error')
            .remove();

    }

    function showFieldError(field, message) {

        field.next('.formnova-error').remove();

        $('<div class="formnova-error"></div>')
            .text(message)
            .insertAfter(field);

    }

    function clearMessage(form) {

        $(form)
            .find('.formnova-message')
            .remove();

    }

    function validateField(field) {

        let value = '';

        let type = (
            field.attr('type') || ''
        ).toLowerCase();

        let required = field.prop('required');

        /*
|--------------------------------------------------------------------------
| File
|--------------------------------------------------------------------------
*/

        if (type === 'file') {

            const input = field[0];

            if (required && input.files.length === 0) {
                return field.data('label') + ' is required.';
            }

            if (!input.files.length) {
                return true;
            }

            const file = input.files[0];

            /*
            |--------------------------------------------------------------------------
            | Extension
            |--------------------------------------------------------------------------
            */

            const allowed = (
                field.data('extensions') || ''
            )
                .toLowerCase()
                .split(',')
                .map(v => $.trim(v))
                .filter(Boolean);

            if (allowed.length) {

                const ext = file.name
                    .split('.')
                    .pop()
                    .toLowerCase();

                if (!allowed.includes(ext)) {

                    return 'Allowed file types: ' + allowed.join(', ').toUpperCase();

                }
            }

            /*
            |--------------------------------------------------------------------------
            | Max Size
            |--------------------------------------------------------------------------
            */

            const maxSize = parseFloat(
                field.data('max-size')
            );

            if (
                maxSize &&
                file.size > (maxSize * 1024 * 1024)
            ) {

                return 'Maximum file size is ' + maxSize + ' MB.';

            }

            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Checkbox
        |--------------------------------------------------------------------------
        */

        if (type === 'checkbox') {

            if (!required) {
                return true;
            }

            let checked = $('input[name="' + field.attr('name') + '"]:checked');

            if (!checked.length) {

                return field.data('label') + ' is required.';

            }

            return true;

        }

        /*
        |--------------------------------------------------------------------------
        | Radio
        |--------------------------------------------------------------------------
        */

        if (type === 'radio') {

            if (!required) {
                return true;
            }

            let checked = $('input[name="' + field.attr('name') + '"]:checked');

            if (!checked.length) {

                return field.data('label') + ' is required.';

            }

            return true;

        }

        value = $.trim(field.val());

        /*
        |--------------------------------------------------------------------------
        | Required
        |--------------------------------------------------------------------------
        */

        if (
            required &&
            value === ''
        ) {

            return field.data('label') + ' is required.';

        }

        /*
        |--------------------------------------------------------------------------
        | Optional Empty Field
        |--------------------------------------------------------------------------
        */

        if (!required && value === '') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        */

        if (
            type === 'email' &&
            value !== ''
        ) {

            let regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!regex.test(value)) {

                return 'Invalid email address.';

            }

        }

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        if (
            type === 'url' &&
            value !== ''
        ) {

            try {

                new URL(value);

            } catch (e) {

                return 'Invalid URL.';

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Phone
        |--------------------------------------------------------------------------
        */

        if (
            type === 'tel' &&
            value !== ''
        ) {

            let regex = /^[0-9+\-\s()]+$/;

            if (!regex.test(value)) {

                return 'Invalid phone number.';

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Minlength
        |--------------------------------------------------------------------------
        */

        let min = field.attr('minlength');

        if (
            min &&
            value.length < parseInt(min)
        ) {

            return 'Minimum ' + min + ' characters required.';

        }

        /*
        |--------------------------------------------------------------------------
        | Maxlength
        |--------------------------------------------------------------------------
        */

        let max = field.attr('maxlength');

        if (
            max &&
            value.length > parseInt(max)
        ) {

            return 'Maximum ' + max + ' characters allowed.';

        }

        return true;

    }

    'use strict';

    $(document).on('submit', '.formnova-frontend', function (e) {

        e.preventDefault();

        var form = this;
        var $form = $(form);
        var $submit = $form.find('[type="submit"]');

        if ($submit.prop('disabled')) {
            return;
        }

        $submit.prop('disabled', true);

        if (!$submit.next('.formnova-loader').length) {

            $submit.after(
                '<span class="formnova-loader"></span>'
            );

        }
        clearMessage(form);

        clearErrors(form);

        let valid = true;

        $form
            .find(':input[name]')
            .each(function () {

                let result = validateField($(this));

                if (result !== true) {

                    showFieldError(
                        $(this),
                        result
                    );

                    this.focus();

                    valid = false;

                    return false;

                }

            });

        if (!valid) {

            $submit.prop('disabled', false);

            $form.find('.formnova-loader').remove();

            return;
        }

        var formData = new FormData(form);

        formData.append(
            'action',
            'ndfb_submit_entry'
        );

        formData.append(
            'form_id',
            $form.data('form-id')
        );

        formData.append(
            'nonce',
            formnova.nonce
        );

        /*
        |--------------------------------------------------------------------------
        | Google reCAPTCHA
        |--------------------------------------------------------------------------
        */

        if (
            typeof FormNovaCaptcha !== 'undefined' &&
            FormNovaCaptcha.enabled
        ) {

            /*
            |--------------------------------------------------------------------------
            | v3
            |--------------------------------------------------------------------------
            */

            if (FormNovaCaptcha.type === 'v3') {

                grecaptcha.ready(function () {

                    grecaptcha.execute(
                        FormNovaCaptcha.site_key,
                        {
                            action: 'submit'
                        }
                    ).then(function (token) {

                        formData.append(
                            'captcha_token',
                            token
                        );

                        sendAjax();

                    });

                });

                return;

            }

            /*
            |--------------------------------------------------------------------------
            | v2
            |--------------------------------------------------------------------------
            */

            const widget = $('.g-recaptcha', form);

            if (widget.length) {

                const token = grecaptcha.getResponse();

                if (!token) {

                    showMessage(
                        form,
                        'Please complete Google reCAPTCHA.',
                        'error'
                    );

                    $submit.prop('disabled', false);

                    $form.find('.formnova-loader').remove();

                    return;
                }

                formData.append(
                    'captcha_token',
                    token
                );
            }

        }

        var fields = {};

        $form
            .find(':input[name]')
            .each(function () {

                var field = $(this);

                if (
                    field.attr('name') === 'g-recaptcha-response'
                ) {
                    return;
                }

                if (
                    field.attr('type') === 'file'
                ) {
                    return;
                }

                if (
                    field.attr('type') === 'checkbox'
                ) {

                    if (!field.is(':checked')) {
                        return;
                    }

                    const name = field.attr('name').replace(/\[\]$/, '').toLowerCase();

                    if (!fields[name]) {
                        fields[name] = [];
                    }

                    fields[name].push(field.val());

                    return;
                }

                if (
                    field.attr('type') === 'radio'
                ) {

                    if (field.is(':checked')) {
                        fields[field.attr('name').toLowerCase()] = field.val();
                    }

                    return;
                }

                fields[field.attr('name').toLowerCase()] = field.val();

            });

        formData.append(
            'data',
            JSON.stringify(fields)
        );

        /*
        |--------------------------------------------------------------------------
        | At least one field must have a value
        |--------------------------------------------------------------------------
        */

        let hasValue = false;

        Object.keys(fields).forEach(function (key) {

            if (key === 'g-recaptcha-response') {
                return;
            }

            const value = fields[key];

            if (Array.isArray(value)) {

                if (value.length) {
                    hasValue = true;
                }

            } else if (
                value !== null &&
                value !== undefined &&
                String(value).trim() !== ''
            ) {

                hasValue = true;

            }

        });

        if (!hasValue) {

            showMessage(
                form,
                'Please fill at least one field.',
                'error'
            );

            $submit.prop('disabled', false);

            $form.find('.formnova-loader').remove();

            return;

        }

        function sendAjax() {

            $.ajax({

                url: formnova.ajax_url,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function (response) {

                    if (response.success) {

                        showMessage(

                            form,

                            response.data.message,

                            'success'

                        );

                        form.reset();

                        if (
                            typeof grecaptcha !== 'undefined' &&
                            $('.g-recaptcha', form).length
                        ) {
                            grecaptcha.reset();
                        }

                    } else {

                        let msg = 'Submission failed.';

                        if (
                            response.data &&
                            response.data.message
                        ) {
                            msg = response.data.message;
                        }

                        showMessage(
                            form,
                            msg,
                            'error'
                        );

                    }

                    if (
                        typeof grecaptcha !== 'undefined' &&
                        $('.g-recaptcha', form).length
                    ) {
                        grecaptcha.reset();
                    }

                    $submit.prop('disabled', false);

                    $form.find('.formnova-loader').remove();

                },

                error: function (xhr) {

                    showMessage(
                        form,
                        xhr.responseJSON?.data?.message || 'Submission failed.',
                        'error'
                    );

                    if (
                        typeof grecaptcha !== 'undefined' &&
                        $('.g-recaptcha', form).length
                    ) {
                        grecaptcha.reset();
                    }

                    $submit.prop('disabled', false);

                    $form.find('.formnova-loader').remove();

                }

            });
        }

        sendAjax();

    });

})(jQuery);