<?php

defined('ABSPATH') || exit;

$formnova_settings = $settings ?? [];

$formnova_captcha = $formnova_settings['captcha'] ?? [];

?>

<div class="wrap">

    <h1>
        <?php esc_html_e('FormNova Settings', 'formnova-form'); ?>
    </h1>

    <?php settings_errors('formnova_settings'); ?>

    <form method="post">

        <?php wp_nonce_field('formnova_save_settings'); ?>

        <input
            type="hidden"
            name="formnova_save_settings"
            value="1">

        <table class="form-table" role="presentation">

            <tbody>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Provider',
                            'formnova-form'
                        );
                        ?>

                    </th>

                    <td>

                        <select name="captcha[provider]">

                            <option
                                value="none"
                                <?php selected(
                                    $formnova_captcha['provider'] ?? 'none',
                                    'none'
                                ); ?>>

                                <?php esc_html_e('None', 'formnova-form'); ?>

                            </option>

                            <option
                                value="v2"
                                <?php selected(
                                    $formnova_captcha['provider'] ?? '',
                                    'v2'
                                ); ?>>

                                Google reCAPTCHA v2

                            </option>

                            <option
                                value="v3"
                                <?php selected(
                                    $formnova_captcha['provider'] ?? '',
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
                            'formnova-form'
                        );
                        ?>

                    </th>

                    <td>

                        <input
                            type="text"
                            class="regular-text"
                            name="captcha[site_key]"
                            value="<?php echo esc_attr(
                                $formnova_captcha['site_key'] ?? ''
                            ); ?>">

                    </td>

                </tr>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Secret Key',
                            'formnova-form'
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
                                $formnova_captcha['secret_key'] ?? ''
                            ); ?>">

                    </td>

                </tr>

                <tr>

                    <th scope="row">

                        <?php
                        esc_html_e(
                            'Minimum Score',
                            'formnova-form'
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
                                $formnova_captcha['score'] ?? '0.5'
                            ); ?>">

                        <p class="description">

                            <?php
                            esc_html_e(
                                'Used only for Google reCAPTCHA v3.',
                                'formnova-form'
                            );
                            ?>

                        </p>

                    </td>

                </tr>

            </tbody>

        </table>

        <?php
        submit_button(
            __('Save Settings', 'formnova-form')
        );
        ?>

    </form>

</div>