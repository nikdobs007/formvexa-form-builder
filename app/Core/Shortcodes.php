<?php
/**
 * Shortcodes handler.
 *
 * @package formvexa
 */

namespace formvexa\Core;

defined('ABSPATH') || exit;

use formvexa\Repository\FormRepository;
use formvexa\Repository\MetaRepository;
use formvexa\Services\FormService;
use formvexa\Services\CaptchaService;

/**
 * Handles all plugin shortcodes.
 */
final class Shortcodes
{

    /**
     * Register shortcodes.
     *
     * @return void
     */
    public function register(): void
    {

        add_shortcode('formvexa_form', [$this, 'render_form']);
    }

    /**
     * Render frontend form via shortcode.
     *
     * Usage:
     * [formvexa_form id="1"]
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public function render_form($atts): string
    {

        $atts = shortcode_atts(
            [
                'id' => 0,
            ],
            $atts
        );

        $captcha = new CaptchaService();

        $form_id = absint($atts['id']);

        if (!$form_id) {
            return '<p>' . esc_html__('Invalid form ID', 'formvexa-form-builder') . '</p>';
        }

        global $wpdb;

        $service = new FormService(
            new FormRepository($wpdb),
            new MetaRepository($wpdb)
        );

        $form = $service->find($form_id);

        if (!$form) {
            return '<p>' . esc_html__(
                'Form not found.',
                'formvexa-form-builder'
            ) . '</p>';
        }

        $builder = $form->builder['builder'] ?? [];
        $settings = $settings = $form->settings ?? [];

        if (empty($builder) || !is_array($builder)) {

            return '
                <div class="formvexa-notice formvexa-notice-warning">
                    ' . esc_html__(
                'Please select at least one field.',
                'formvexa-form-builder'
            ) . '
                </div>
            ';

        }

        ob_start();
        ?>

        <form class="formvexa-frontend" method="post" enctype="multipart/form-data"
            data-form-id="<?php echo esc_attr($form_id); ?>">

            <?php

            foreach ($builder as $field) {

                if (empty($field['type'])) {
                    continue;
                }

                $definition = \formvexa\Fields\Registry::get(
                    $field['type']
                );

                if (!$definition) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Fresh instance with Builder Data
                |--------------------------------------------------------------------------
                */

                $class = get_class($definition);

                $instance = new $class($field);

                ?>

                <div class="fn-field <?php echo esc_attr($field['class'] ?? ''); ?>">


                    <?php if (!empty($field['label'])): ?>

                        <label for="<?php echo esc_attr($field['id']); ?>">

                            <?php

                            echo esc_html(
                                $field['label']
                            );

                            ?>

                            <?php if (!empty($field['required'])): ?>

                                <span class="fn-required">
                                    *
                                </span>

                            <?php endif; ?>


                        </label>


                    <?php endif; ?>


                    <?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in each field's render() method.
                    echo $instance->render();

                    ?>


                    <?php if (!empty($field['description'])): ?>

                        <p class="fn-description">

                            <?php

                            echo esc_html(
                                $field['description']
                            );

                            ?>

                        </p>


                    <?php endif; ?>


                </div>

                <?php
            }

            ?>

            <?php
            if (
                !empty($settings['advanced']['captcha_enabled']) &&
                $captcha->get_type() === 'v2'
            ):
                ?>
                <div class="formvexa-captcha">
                    <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($captcha->get_site_key()); ?>">
                    </div>
                </div>
            <?php endif; ?>

            <div class="fn-field" style="display:none !important;">

                <input
                    type="text"
                    id="formvexa_hp"
                    name="formvexa_hp"
                    tabindex="-1"
                    autocomplete="off"
                />

            </div>

            <button type="submit" class="formvexa-submit-button">

                Submit

            </button>

        </form>

        <?php
        return ob_get_clean();
    }
}