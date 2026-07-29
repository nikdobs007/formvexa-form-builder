<?php

namespace FormNova\Controllers\Ajax;

defined('ABSPATH') || exit;

use FormNova\Services\CaptchaService;

final class SettingsAjaxController
{
    public function register(): void
    {
        add_action(
            'wp_ajax_formnova_test_captcha',
            [$this, 'test_captcha']
        );
    }

    public function test_captcha(): void
    {
        check_ajax_referer(
            'ndfb_nonce',
            'nonce'
        );

        if (!current_user_can('manage_options')) {

            wp_send_json_error(
                [
                    'message' => __('Permission denied.', 'formnova-form'),
                ],
                403
            );
        }

        $service = new CaptchaService();

        $result = $service->test_configuration();

        if (is_wp_error($result)) {

            wp_send_json_error(
                [
                    'message' => $result->get_error_message(),
                ]
            );
        }

        wp_send_json_success(
            [
                'message' => __('Google reCAPTCHA configuration looks good.', 'formnova-form'),
            ]
        );
    }
}