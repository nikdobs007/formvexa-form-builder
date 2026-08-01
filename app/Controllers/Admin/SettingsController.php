<?php

namespace formvexa\Controllers\Admin;

defined('ABSPATH') || exit;

use formvexa\Core\View;

final class SettingsController
{
    /**
     * Option name.
     *
     * @var string
     */
    private string $option_name = 'formvexa_settings';

    /**
     * Display settings page.
     */
    public function index(): void
    {
        if (
            isset($_POST['formvexa_save_settings']) &&
            check_admin_referer('formvexa_save_settings')
        ) {
            $this->save();
        }

        $settings = get_option(
            $this->option_name,
            []
        );

        View::render(
            'admin/settings/index',
            [
                'settings' => $settings,
            ]
        );
    }

    /**
     * Save settings.
     */
    private function save(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(
                esc_html__(
                    'You are not allowed to perform this action.',
                    'formvexa-form-builder'
                )
            );
        }

        $captcha = wp_unslash(
            $_POST['captcha'] ?? []
        );

        $provider = sanitize_key(
            $captcha['provider'] ?? 'none'
        );

        if (
            !in_array(
                $provider,
                ['none', 'v2', 'v3'],
                true
            )
        ) {
            $provider = 'none';
        }

        $score = (float) (
            $captcha['score'] ?? 0.5
        );

        $score = max(
            0.1,
            min(
                1.0,
                $score
            )
        );

        $settings = get_option(
            $this->option_name,
            []
        );

        $settings['captcha'] = [

            'enabled' => !empty($captcha['enabled']),

            'provider' => $provider,

            'site_key' => sanitize_text_field(
                $captcha['site_key'] ?? ''
            ),

            'secret_key' => sanitize_text_field(
                $captcha['secret_key'] ?? ''
            ),

            'score' => $score,

        ];

        update_option(
            $this->option_name,
            $settings,
            false
        );

        add_settings_error(
            'formvexa_settings',
            'settings_saved',
            __(
                'Settings saved successfully.',
                'formvexa-form-builder'
            ),
            'updated'
        );
    }
}