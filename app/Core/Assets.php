<?php

namespace FormNova\Core;

defined('ABSPATH') || exit;

use FormNova\Services\FieldSchemaService;
use FormNova\Services\CaptchaService;

/**
 * Assets loader
 */
final class Assets
{

    /**
     * Register hooks.
     */
    public function register(): void
    {

        add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_assets']);
    }

    /**
     * Admin assets.
     */
    public function admin_assets(string $hook_suffix): void
    {
        wp_enqueue_style(
            'formnova-admin',
            NDFB_PLUGIN_URL . 'assets/css/admin.css',
            [],
            NDFB_VERSION
        );

        wp_enqueue_script(
            'formnova-admin',
            NDFB_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            NDFB_VERSION,
            true
        );

        wp_localize_script(
            'formnova-admin',
            'FormNova',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ndfb_nonce'),
            ]
        );

        $this->builder_assets();
    }

    /**
     * Builder assets.
     */
    private function builder_assets(): void
    {
        $base = NDFB_PLUGIN_URL . 'assets/js/builder/';

        wp_enqueue_script(
            'formnova-builder-registry',
            $base . 'registry.js',
            [],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-fields',
            $base . 'fields.js',
            ['formnova-builder-registry'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-state',
            $base . 'state.js',
            ['formnova-builder-fields'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-canvas',
            $base . 'canvas.js',
            ['formnova-builder-state'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-properties',
            $base . 'properties.js',
            ['formnova-builder-canvas'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-mail',
            $base . 'mail.js',
            ['formnova-builder-properties'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-advanced',
            $base . 'advanced.js',
            ['formnova-builder-properties'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-dragdrop',
            $base . 'dragdrop.js',
            ['formnova-builder-mail'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder-tabs',
            $base . 'tabs.js',
            ['formnova-builder-dragdrop'],
            NDFB_VERSION,
            true
        );

        wp_enqueue_script(
            'formnova-builder',
            $base . 'builder.js',
            ['formnova-builder-tabs'],
            NDFB_VERSION,
            true
        );
    }

    /**
     * Frontend assets.
     */
    /**
     * Frontend assets.
     */
    /**
     * Frontend assets.
     */
    public function frontend_assets(): void
    {
        wp_enqueue_style(
            'formnova-frontend',
            NDFB_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            NDFB_VERSION
        );

        wp_enqueue_script(
            'formnova-frontend',
            NDFB_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            NDFB_VERSION,
            true
        );

        /*
        |--------------------------------------------------------------------------
        | Google reCAPTCHA
        |--------------------------------------------------------------------------
        */

        $captcha = new CaptchaService();

        if ($captcha->get_type() === 'v3') {

            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js?render=' .
                rawurlencode($captcha->get_site_key()),
                [],
                NDFB_VERSION,
                true
            );

        } else {

            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js',
                [],
                NDFB_VERSION,
                true
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Frontend JS Data
        |--------------------------------------------------------------------------
        */

        wp_localize_script(
            'formnova-frontend',
            'formnova',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('formnova_submit'),
            ]
        );

        wp_localize_script(
            'formnova-frontend',
            'FormNovaCaptcha',
            $captcha->frontend()
        );
    }
}