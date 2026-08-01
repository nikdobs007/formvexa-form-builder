<?php
/**
 * Google reCAPTCHA Service.
 *
 * @package formvexa
 */

namespace formvexa\Services;

defined('ABSPATH') || exit;

use WP_Error;

final class CaptchaService
{

    /**
     * Settings.
     *
     * @var array
     */
    private array $settings = [];

    /**
     * Constructor.
     */
    public function __construct()
    {

        $this->settings = get_option(
            'formvexa_settings',
            []
        );
    }

    /**
     * Get captcha type.
     *
     * @return string
     */
    public function get_type(): string
    {

        return sanitize_key(
            $this->settings['captcha']['type']
            ?? 'v2'
        );

    }

    /**
     * Get Site Key.
     *
     * @return string
     */
    public function get_site_key(): string
    {

        return sanitize_text_field(
            $this->settings['captcha']['site_key']
            ?? ''
        );

    }

    /**
     * Get Secret Key.
     *
     * @return string
     */
    public function get_secret_key(): string
    {

        return sanitize_text_field(
            $this->settings['captcha']['secret_key']
            ?? ''
        );

    }

    /**
     * Get Score.
     *
     * @return float
     */
    public function get_score(): float
    {

        return (float) (
            $this->settings['captcha']['score']
            ?? 0.5
        );

    }

    /**
     * Frontend config.
     *
     * @return array
     */
    public function frontend(): array
    {

        return [
            'type' => $this->get_type(),
            'site_key' => $this->get_site_key(),
            'score' => $this->get_score(),
        ];

    }

    /**
     * Verify captcha.
     *
     * @param string $token Token.
     *
     * @return true|WP_Error
     */
    public function verify(string $token)
    {
        if (empty($token)) {

            return new WP_Error(
                'captcha_required',
                __('Captcha verification is required.', 'formvexa-form-builder')
            );

        }

        if ('v3' === $this->get_type()) {
            return $this->verify_v3($token);
        }

        return $this->verify_v2($token);
    }

    /**
     * Verify reCAPTCHA v2.
     *
     * @param string $token Token.
     *
     * @return true|\WP_Error
     */
    private function verify_v2(string $token)
    {
        $response = $this->request_google($token);

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['success'])) {

            return new \WP_Error(
                'captcha_failed',
                __('Captcha verification failed.', 'formvexa-form-builder')
            );
        }

        $hostname = wp_parse_url(home_url(), PHP_URL_HOST);

        if (
            $hostname !== 'localhost' &&
            !empty($response['hostname']) &&
            $hostname !== $response['hostname']
        ) {

            return new \WP_Error(
                'captcha_hostname',
                __('Invalid captcha hostname.', 'formvexa-form-builder')
            );
        }

        return true;
    }

    /**
     * Verify reCAPTCHA v3.
     *
     * @param string $token Captcha token.
     *
     * @return true|\WP_Error
     */
    private function verify_v3(string $token)
    {
        $response = $this->request_google($token);

        if (is_wp_error($response)) {
            return $response;
        }

        if (empty($response['success'])) {

            return new \WP_Error(
                'captcha_failed',
                __('Captcha verification failed.', 'formvexa-form-builder')
            );
        }

        $hostname = wp_parse_url(home_url(), PHP_URL_HOST);

        if (
            $hostname !== 'localhost' &&
            !empty($response['hostname']) &&
            $hostname !== $response['hostname']
        ) {

            return new \WP_Error(
                'captcha_hostname',
                __('Invalid captcha hostname.', 'formvexa-form-builder')
            );
        }

        if (
            !empty($response['action']) &&
            'submit' !== $response['action']
        ) {

            return new \WP_Error(
                'captcha_action',
                __('Invalid captcha action.', 'formvexa-form-builder')
            );
        }

        if (
            empty($response['score']) ||
            (float) $response['score'] < $this->get_score()
        ) {

            return new \WP_Error(
                'captcha_score',
                __('Captcha verification score is too low.', 'formvexa-form-builder')
            );
        }

        return true;
    }


    /**
     * Request Google Verification API.
     *
     * @param string $token Token.
     *
     * @return array|\WP_Error
     */
    private function request_google(string $token)
    {
        $secret = $this->get_secret_key();

        if (empty($secret)) {

            return new \WP_Error(
                'captcha_secret_missing',
                __('Captcha Secret Key is missing.', 'formvexa-form-builder')
            );
        }

        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'timeout' => 15,
                'sslverify' => true,
                'body' => [
                    'secret' => $secret,
                    'response' => sanitize_text_field($token),
                    'remoteip' => sanitize_text_field(
                        wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')
                    ),
                ],
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);

        if (200 !== $code) {

            return new \WP_Error(
                'captcha_http',
                __('Captcha server returned an invalid response.', 'formvexa-form-builder')
            );
        }

        $body = wp_remote_retrieve_body($response);

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {

            return new \WP_Error(
                'captcha_invalid_response',
                __('Invalid captcha response.', 'formvexa-form-builder')
            );
        }

        if (
            !empty($decoded['error-codes']) &&
            is_array($decoded['error-codes'])
        ) {

            return new \WP_Error(
                'captcha_google_error',
                implode(', ', array_map('sanitize_text_field', $decoded['error-codes']))
            );
        }

        return $decoded;
    }

    /**
     * Create WP_Error from Google error codes.
     *
     * @param array $response Google response.
     *
     * @return \WP_Error
     */
    private function create_error(array $response): \WP_Error
    {
        $errors = $response['error-codes'] ?? [];

        if (!is_array($errors)) {
            $errors = [];
        }

        $message = __('Captcha verification failed.', 'formvexa-form-builder');

        foreach ($errors as $code) {

            switch ($code) {

                case 'missing-input-secret':
                    $message = __('Captcha secret key is missing.', 'formvexa-form-builder');
                    break;

                case 'invalid-input-secret':
                    $message = __('Invalid captcha secret key.', 'formvexa-form-builder');
                    break;

                case 'missing-input-response':
                    $message = __('Captcha response is missing.', 'formvexa-form-builder');
                    break;

                case 'invalid-input-response':
                    $message = __('Invalid captcha response.', 'formvexa-form-builder');
                    break;

                case 'timeout-or-duplicate':
                    $message = __('Captcha expired. Please try again.', 'formvexa-form-builder');
                    break;

                case 'bad-request':
                    $message = __('Invalid captcha request.', 'formvexa-form-builder');
                    break;
            }

            break;
        }

        return new \WP_Error(
            'captcha_failed',
            $message
        );
    }

    /**
     * Test Google reCAPTCHA configuration.
     *
     * @return true|\WP_Error
     */
    public function test_configuration()
    {
        if (!$this->is_enabled()) {

            return new \WP_Error(
                'captcha_disabled',
                __('Captcha is disabled.', 'formvexa-form-builder')
            );
        }

        if (empty($this->get_site_key())) {

            return new \WP_Error(
                'site_key',
                __('Site Key is missing.', 'formvexa-form-builder')
            );
        }

        if (empty($this->get_secret_key())) {

            return new \WP_Error(
                'secret_key',
                __('Secret Key is missing.', 'formvexa-form-builder')
            );
        }

        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'timeout' => 15,
                'body' => [
                    'secret' => $this->get_secret_key(),
                    'response' => 'test',
                ],
            ]
        );

        if (is_wp_error($response)) {
            return $response;
        }

        if (200 !== wp_remote_retrieve_response_code($response)) {

            return new \WP_Error(
                'google',
                __('Unable to connect to Google.', 'formvexa-form-builder')
            );
        }

        return true;
    }
}