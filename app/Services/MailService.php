<?php
/**
 * Mail Service.
 *
 * @package formvexa
 */

namespace formvexa\Services;

defined('ABSPATH') || exit;

final class MailService
{
    /**
     * Send Mail.
     *
     * @param string       $to
     * @param string       $subject
     * @param string       $message
     * @param array|string $headers
     * @param array        $attachments
     *
     * @return bool
     */
    public function send(
        int $form_id,
        int $entry_id,
        array $fields
    ): bool {

        $formService = new FormService(
            new \formvexa\Repository\FormRepository($GLOBALS['wpdb']),
            new \formvexa\Repository\MetaRepository($GLOBALS['wpdb'])
        );

        $settings = $formService->getMeta(
            $form_id,
            'settings'
        );

        if (!is_array($settings)) {
            return false;
        }

        $admin = $this->send_admin(
            $settings,
            $fields
        );

        $user = $this->send_user(
            $settings,
            $fields
        );

        return ($admin || $user);
    }

    /**
     * Send Admin Mail.
     *
     * @param array $settings
     * @param array $fields
     *
     * @return bool
     */
    public function send_admin(
        array $settings,
        array $fields
    ): bool {

        $mail = $settings['mail'] ?? [];

        if (empty($mail['admin_to'][0])) {
            return false;
        }

        $subject = $this->replace_tags(
            $mail['admin_subject'] ?? 'New Form Submission',
            $fields
        );

        $message = $this->replace_tags(
            $mail['admin_message'] ?? '',
            $fields
        );

        $headers = [];
        $attachments = [];

        if (!empty($mail['attach_files'])) {

            $upload = wp_upload_dir();

            foreach ($fields as $value) {

                if (
                    is_string($value)
                    && strpos($value, $upload['baseurl']) === 0
                ) {

                    $file = str_replace(
                        $upload['baseurl'],
                        $upload['basedir'],
                        $value
                    );

                    if (file_exists($file)) {
                        $attachments[] = $file;
                    }
                }
            }
        }

        if (!empty($mail['from_email'])) {

            $headers[] = 'From: ' .
                ($mail['from_name'] ?? get_bloginfo('name'))
                .
                ' <' .
                $mail['from_email']
                .
                '>';

        }

        if (!empty($mail['admin_cc'])) {
            $headers[] = 'Cc: ' . implode(',', $mail['admin_cc']);
        }

        if (!empty($mail['admin_bcc'])) {
            $headers[] = 'Bcc: ' . implode(',', $mail['admin_bcc']);
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        return wp_mail(
            $mail['admin_to'][0],
            $subject,
            wpautop($message),
            $headers,
            $attachments
        );
    }

    /**
     * Send User Mail.
     *
     * @param array $settings
     * @param array $fields
     *
     * @return bool
     */
    public function send_user(
        array $settings,
        array $fields
    ): bool {

        $mail = $settings['mail'] ?? [];

        // User email notification enabled?
        if (empty($mail['user_enabled'])) {
            return false;
        }

        // Which field contains user email?
        if (empty($mail['user_email_field'])) {
            return false;
        }

        $emailField = $mail['user_email_field'];

        // Email field exists?
        if (empty($fields[$emailField])) {
            return false;
        }

        $subject = $this->replace_tags(
            $mail['user_subject'] ?? '',
            $fields
        );

        $message = $this->replace_tags(
            $mail['user_message'] ?? '',
            $fields
        );

        $headers = [];

        if (!empty($mail['from_email'])) {

            $headers[] =
                'From: '
                . ($mail['from_name'] ?: get_bloginfo('name'))
                . ' <'
                . $mail['from_email']
                . '>';

        }

        if (!empty($mail['reply_to'])) {
            $headers[] = 'Reply-To: ' . $mail['reply_to'];
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $result = wp_mail(
            sanitize_email($fields[$emailField]),
            $subject,
            wpautop($message),
            $headers
        );

        return $result;
    }

    /**
     * Replace Tags.
     *
     * {name}
     * {email}
     * {phone}
     * {all_fields}
     *
     * @param string $text
     * @param array  $fields
     *
     * @return string
     */
    public function replace_tags(
        string $text,
        array $fields
    ): string {

        foreach ($fields as $key => $value) {

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $text = str_replace(
                '{' . $key . '}',
                (string) $value,
                $text
            );

        }

        $all = '';

        foreach ($fields as $key => $value) {

            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $all .= sprintf(
                '<p><strong>%s :</strong> %s</p>',
                esc_html($key),
                esc_html((string) $value)
            );

        }

        $text = str_replace(
            '{all_fields}',
            $all,
            $text
        );

        $text = str_replace('{site_name}', get_bloginfo('name'), $text);

        $text = str_replace('{site_url}', home_url(), $text);

        $text = str_replace('{date}', wp_date('d-m-Y'), $text);

        $text = str_replace('{time}', wp_date('H:i:s'), $text);

        if (!empty($fields['form_title'])) {
            $text = str_replace(
                '{form_title}',
                $fields['form_title'],
                $text
            );
        }

        return $text;
    }
}