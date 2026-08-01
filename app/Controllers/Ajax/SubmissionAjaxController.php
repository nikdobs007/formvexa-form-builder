<?php
/**
 * Submission Ajax Controller.
 *
 * @package formvexa
 */

namespace formvexa\Controllers\Ajax;

defined('ABSPATH') || exit;

use formvexa\Services\EntryService;
use formvexa\Services\FormService;
use formvexa\Services\CaptchaService;

final class SubmissionAjaxController
{
    /**
     * Entry service.
     *
     * @var EntryService
     */
    private EntryService $entryService;

    /**
     * Form service.
     *
     * @var FormService
     */
    private FormService $formService;

    /**
     * Captcha service.
     *
     * @var CaptchaService
     */
    private CaptchaService $captchaService;

    /**
     * Constructor.
     *
     * @param EntryService $entryService Entry service.
     * @param FormService  $formService  Form service.
     */
    public function __construct(
        EntryService $entryService,
        FormService $formService,
        CaptchaService $captchaService
    ) {

        $this->entryService = $entryService;

        $this->formService = $formService;

        $this->captchaService = $captchaService;
    }

    /**
     * Register ajax hooks.
     *
     * @return void
     */
    public function register(): void
    {
        add_action(
            'wp_ajax_ndfb_submit_entry',
            [$this, 'submit']
        );

        add_action(
            'wp_ajax_nopriv_ndfb_submit_entry',
            [$this, 'submit']
        );
    }

    /**
     * Submit form.
     *
     * @return void
     */
    public function submit(): void
    {
        if (
            !check_ajax_referer(
                'formvexa_submit',
                'nonce',
                false
            )
        ) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Security check failed.',
                        'formvexa-form-builder'
                    ),
                ],
                403
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Honeypot
        |--------------------------------------------------------------------------
        */

        if (
            !empty($_POST['formvexa_hp'])
        ) {

            wp_send_json_error(
                [
                    'message' => __(
                        'Spam detected.',
                        'formvexa-form-builder'
                    ),
                ],
                400
            );

        }

        $form_id = isset($_POST['form_id'])
            ? absint(
                wp_unslash($_POST['form_id'])
            )
            : 0;

        if ($form_id <= 0) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Invalid form.',
                        'formvexa-form-builder'
                    ),
                ],
                400
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Rate Limit (5 submissions / minute per IP)
        |--------------------------------------------------------------------------
        */

        $ip = $this->get_client_ip();

        $key = 'formvexa_rate_' . $form_id . '_' . md5($ip);

        $count = get_transient($key);

        if (false === $count) {

            set_transient(
                $key,
                1,
                MINUTE_IN_SECONDS
            );

        } else {

            if ($count >= 5) {

                wp_send_json_error(
                    [
                        'message' => __(
                            'Too many submissions. Please wait one minute and try again.',
                            'formvexa-form-builder'
                        ),
                    ],
                    429
                );
            }

            set_transient(
                $key,
                $count + 1,
                MINUTE_IN_SECONDS
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Load Form
        |--------------------------------------------------------------------------
        */

        $form = $this->formService->find($form_id);

        if (!$form) {

            wp_send_json_error(
                [
                    'message' => __('Form not found.', 'formvexa-form-builder'),
                ],
                404
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Google Captcha Verification
        |--------------------------------------------------------------------------
        */

        if (!empty($form->settings['advanced']['captcha_enabled'])) {

            $captcha_token = '';

            if (!empty($_POST['captcha_token'])) {

                $captcha_token = sanitize_text_field(
                    wp_unslash($_POST['captcha_token'])
                );

            } elseif (!empty($_POST['g-recaptcha-response'])) {

                $captcha_token = sanitize_text_field(
                    wp_unslash($_POST['g-recaptcha-response'])
                );

            }

            $captcha = $this->captchaService->verify(
                $captcha_token
            );

            if (is_wp_error($captcha)) {

                wp_send_json_error(
                    [
                        'message' => $captcha->get_error_message(),
                    ],
                    400
                );

            }

        }

        $raw = isset($_POST['data'])
            ? wp_unslash($_POST['data'])
            : '';

        if (empty($raw)) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Submission data missing.',
                        'formvexa-form-builder'
                    ),
                ],
                400
            );
        }

        $request = json_decode(
            $raw,
            true
        );

        if (
            JSON_ERROR_NONE !== json_last_error()
            || !is_array($request)
        ) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Invalid request payload.',
                        'formvexa-form-builder'
                    ),
                ],
                400
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sanitize Request Data
        |--------------------------------------------------------------------------
        */

        $request = $this->sanitize_request_data(
            $request
        );

        /*
        |--------------------------------------------------------------------------
        | Load Builder Fields
        |--------------------------------------------------------------------------
        */

        $builder = $this->formService->get_builder(
            $form_id
        );

        if (empty($builder)) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Form builder not found.',
                        'formvexa-form-builder'
                    ),
                ],
                404
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Store Entry
        |--------------------------------------------------------------------------
        */

        $result = $this->entryService->store_entry(
            $form_id,
            $builder,
            $request,
            $_FILES
        );

        if (is_wp_error($result)) {
            wp_send_json_error(
                [
                    'message' => $result->get_error_message(),
                ],
                400
            );
        }

        wp_send_json_success(
            [
                'entry_id' => (int) $result,
                'message' => __(
                    'Form submitted successfully.',
                    'formvexa-form-builder'
                ),
            ]
        );
    }

    /**
     * Get real client IP address.
     *
     * @return string
     */
    private function get_client_ip(): string
    {
        // Cloudflare trusted header.
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {

            $ip = sanitize_text_field(
                wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP'])
            );

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        // Default web server IP.
        if (!empty($_SERVER['REMOTE_ADDR'])) {

            $ip = sanitize_text_field(
                wp_unslash($_SERVER['REMOTE_ADDR'])
            );

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '';
    }

    /**
     * Recursively sanitize submitted request data.
     *
     * @param mixed $data Request data.
     *
     * @return mixed
     */
    private function sanitize_request_data($data)
    {
        if (!is_array($data)) {

            if (is_bool($data) || is_numeric($data) || $data === null) {
                return $data;
            }

            return sanitize_text_field((string) $data);
        }

        foreach ($data as $key => $value) {

            if (is_array($value)) {
                $data[$key] = $this->sanitize_request_data($value);
                continue;
            }

            if (is_bool($value) || is_numeric($value) || $value === null) {
                $data[$key] = $value;
                continue;
            }

            if (is_string($value)) {

                if (strpos($key, 'email') !== false) {
                    $data[$key] = sanitize_email($value);

                } elseif (strpos($key, 'url') !== false) {
                    $data[$key] = esc_url_raw($value);

                } else {
                    $data[$key] = sanitize_text_field($value);
                }
            }
        }

        return $data;
    }
}