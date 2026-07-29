<?php
/**
 * Submission Ajax Controller.
 *
 * @package FormNova
 */

namespace FormNova\Controllers\Ajax;

defined('ABSPATH') || exit;

use FormNova\Services\EntryService;
use FormNova\Services\FormService;
use FormNova\Services\CaptchaService;

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
                'formnova_submit',
                'nonce',
                false
            )
        ) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Security check failed.',
                        'formnova-form'
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
            !empty($_POST['formnova_hp'])
        ) {

            wp_send_json_error(
                [
                    'message' => __(
                        'Spam detected.',
                        'formnova-form'
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
                        'formnova-form'
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

        $key = 'formnova_rate_' . $form_id . '_' . md5($ip);

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
                            'formnova-form'
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
                    'message' => __('Form not found.', 'formnova-form'),
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
                        'formnova-form'
                    ),
                ],
                400
            );
        }

        $request = json_decode(
            $raw,
            true
        );

        if (!is_array($request)) {
            wp_send_json_error(
                [
                    'message' => __(
                        'Invalid request payload.',
                        'formnova-form'
                    ),
                ],
                400
            );
        }

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
                        'formnova-form'
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
                    'formnova-form'
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
        $keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy / Load Balancer
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {

            if (empty($_SERVER[$key])) {
                continue;
            }

            $server_value = sanitize_text_field(
                wp_unslash(
                    $_SERVER[$key]
                )
            );

            $ip_list = explode(
                ',',
                $server_value
            );

            foreach ($ip_list as $ip) {

                $ip = trim($ip);

                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return sanitize_text_field($ip);
                }
            }
        }

        return '';
    }
}