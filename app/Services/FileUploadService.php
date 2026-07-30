<?php

namespace FormNova\Services;

defined('ABSPATH') || exit;

use WP_Error;

final class FileUploadService
{
    /**
     * Upload single file.
     */
    public function upload(
        array $file = [],
        array $field = []
    ) {

        $result = $this->validate(
            $file,
            $field
        );

        if (is_wp_error($result)) {
            return $result;
        }

        if (
            empty($file) ||
            (int) $file['error'] === UPLOAD_ERR_NO_FILE
        ) {
            return '';
        }

        if (
            empty($file) ||
            !isset($file['error'])
        ) {
            return '';
        }

        /*
        |--------------------------------------------------------------------------
        | Upload
        |--------------------------------------------------------------------------
        */

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $upload = wp_handle_upload(
            $file,
            [
                'test_form' => false,
                'test_type' => true,
            ]
        );

        if (isset($upload['error'])) {

            return new WP_Error(
                'upload_error',
                $upload['error']
            );
        }

        return esc_url_raw(
            $upload['url']
        );
    }

    /**
     * Validate uploaded file.
     *
     * @return true|WP_Error
     */
    public function validate(
        array $file = [],
        array $field = []
    ) {

        if (
            empty($file) ||
            !isset($file['error'])
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | No File Selected
        |--------------------------------------------------------------------------
        */

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Upload Error
        |--------------------------------------------------------------------------
        */

        if ($file['error'] !== UPLOAD_ERR_OK) {

            return new WP_Error(
                'upload_error',
                sprintf(
                    /* translators: %d: PHP file upload error code. */
                    __('File upload failed. Error Code: %d', 'formnova-form-builder'),
                    (int) $file['error']
                )
            );
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return new \WP_Error(
                'invalid_upload',
                __('Invalid uploaded file.', 'formnova-form-builder')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Max Size
        |--------------------------------------------------------------------------
        */

        $max_size = !empty($field['max_size'])
            ? ((int) $field['max_size'] * 1024 * 1024)
            : (5 * 1024 * 1024);

        if ($file['size'] > $max_size) {

            return new WP_Error(
                'file_size',
                sprintf(
                    /* translators: %d: Maximum allowed file size in megabytes. */
                    __('Maximum file size is %d MB.', 'formnova-form-builder'),
                    !empty($field['max_size'])
                    ? (int) $field['max_size']
                    : 5
                )
            );
        }

        /*
|--------------------------------------------------------------------------
| Dangerous Extensions (Always Block)
|--------------------------------------------------------------------------
*/

        $dangerous_extensions = [
            'php',
            'php3',
            'php4',
            'php5',
            'php7',
            'php8',
            'phtml',
            'phar',
            'cgi',
            'pl',
            'py',
            'sh',
            'exe',
            'dll',
            'bat',
            'cmd',
            'com',
            'js',
            'html',
            'htm',
            'svg',
        ];

        $file['name'] = sanitize_file_name($file['name']);

        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );

        if (in_array($extension, $dangerous_extensions, true)) {

            return new WP_Error(
                'dangerous_extension',
                __('This file type is not allowed.', 'formnova-form-builder')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Extension Validation
        |--------------------------------------------------------------------------
        */

        $allowed_extensions = [];

        if (!empty($field['allowed_extensions'])) {

            $allowed_extensions = array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        strtolower($field['allowed_extensions'])
                    )
                )
            );
        }

        if (
            !empty($allowed_extensions) &&
            !in_array(
                $extension,
                $allowed_extensions,
                true
            )
        ) {

            return new WP_Error(
                'file_extension',
                sprintf(
                    /* translators: %s: Comma-separated list of allowed file extensions. */
                    __('Allowed file types: %s', 'formnova-form-builder'),
                    strtoupper(implode(', ', $allowed_extensions))
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME Validation
        |--------------------------------------------------------------------------
        */

        $real = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name']
        );

        $mime = strtolower($real['type'] ?? '');

        if (empty($real['ext']) || empty($real['type'])) {
            return new WP_Error(
                'file_validation',
                __('The uploaded file could not be validated.', 'formnova-form-builder')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | WordPress MIME Whitelist
        |--------------------------------------------------------------------------
        */

        $wp_allowed_mimes = array_map(
            'strtolower',
            get_allowed_mime_types()
        );

        if (
            empty($mime) ||
            !in_array($mime, $wp_allowed_mimes, true)
        ) {

            return new WP_Error(
                'file_mime',
                __('This file type is not supported by WordPress.', 'formnova-form-builder')
            );
        }

        if (!empty($field['allowed_mimes'])) {

            $allowed_mimes = array_filter(
                array_map(
                    'trim',
                    explode(
                        ',',
                        strtolower($field['allowed_mimes'])
                    )
                )
            );

            if (
                !empty($allowed_mimes) &&
                !in_array(
                    $mime,
                    $allowed_mimes,
                    true
                )
            ) {

                return new WP_Error(
                    'file_mime',
                    __('Uploaded file type is not allowed.', 'formnova-form-builder')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Real Image (Only If Image Extension Allowed)
        |--------------------------------------------------------------------------
        */

        $image_extensions = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'bmp',
            'avif',
        ];

        $field_extensions = array_filter(
            array_map(
                'trim',
                explode(
                    ',',
                    strtolower((string) ($field['allowed_extensions'] ?? ''))
                )
            )
        );

        if (
            !empty(array_intersect($field_extensions, $image_extensions))
        ) {

            if (false === getimagesize($file['tmp_name'])) {

                return new WP_Error(
                    'invalid_image',
                    __('Invalid image file.', 'formnova-form-builder')
                );
            }
        }

        return true;
    }
}