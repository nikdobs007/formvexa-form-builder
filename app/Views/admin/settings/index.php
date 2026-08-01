<?php

defined('ABSPATH') || exit;

$formvexa_settings = $settings ?? [];

$formvexa_captcha = $formvexa_settings['captcha'] ?? [];

?>

<div class="wrap">

    <h1>
        <?php esc_html_e('formvexa Settings', 'formvexa-form-builder'); ?>
    </h1>

    <?php settings_errors('formvexa_settings'); ?>

    <form method="post">

        <?php wp_nonce_field('formvexa_save_settings'); ?>

        <input
            type="hidden"
            name="formvexa_save_settings"
            value="1">

        <table class="form-table" role="presentation">

            <tbody>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Provider',
                            'formvexa-form-builder'
                        );
                        ?>

                    </th>

                    <td>

                        <select name="captcha[provider]">

                            <option
                                value="none"
                                <?php selected(
                                    $formvexa_captcha['provider'] ?? 'none',
                                    'none'
                                ); ?>>

                                <?php esc_html_e('None', 'formvexa-form-builder'); ?>

                            </option>

                            <option
                                value="v2"
                                <?php selected(
                                    $formvexa_captcha['provider'] ?? '',
                                    'v2'
                                ); ?>>

                                Google reCAPTCHA v2

                            </option>

                            <option
                                value="v3"
                                <?php selected(
                                    $formvexa_captcha['provider'] ?? '',
                                    'v3'
                                ); ?>>

                                Google reCAPTCHA v3

                            </option>

                        </select>

                    </td>

                </tr>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Site Key',
                            'formvexa-form-builder'
                        );
                        ?>

                    </th>

                    <td>

                        <input
                            type="text"
                            class="regular-text"
                            name="captcha[site_key]"
                            value="<?php echo esc_attr(
                                $formvexa_captcha['site_key'] ?? ''
                            ); ?>">

                    </td>

                </tr>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Secret Key',
                            'formvexa-form-builder'
                        );
                        ?>

                    </th>

                    <td>

                        <input
                            type="password"
                            class="regular-text"
                            autocomplete="off"
                            name="captcha[secret_key]"
                            value="<?php echo esc_attr(
                                $formvexa_captcha['secret_key'] ?? ''
                            ); ?>">

                    </td>

                </tr>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Minimum Score',
                            'formvexa-form-builder'
                        );
                        ?>

                    </th>

                    <td>

                        <input
                            type="number"
                            step="0.1"
                            min="0.1"
                            max="1"
                            name="captcha[score]"
                            value="<?php echo esc_attr(
                                $formvexa_captcha['score'] ?? '0.5'
                            ); ?>">

                        <p class="description">

                            <?php
                            esc_html_e(
                                'Used only for Google reCAPTCHA v3.',
                                'formvexa-form-builder'
                            );
                            ?>

                        </p>

                    </td>

                </tr>

            </tbody>

        </table>

        <?php
        submit_button(
            __('Save Settings', 'formvexa-form-builder')
        );
        ?>

    </form>

</div>