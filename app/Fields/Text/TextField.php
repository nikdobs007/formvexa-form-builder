<?php

namespace formvexa\Fields\Text;

defined('ABSPATH') || exit;

use formvexa\Fields\BaseField;
use formvexa\Fields\Schema\Setting;

final class TextField extends BaseField
{
    /**
     * Field type.
     *
     * @return string
     */
    public function type(): string
    {
        return 'text';
    }

    /**
     * Field title.
     *
     * @return string
     */
    public function title(): string
    {
        return __('Text', 'formvexa-form-builder');
    }

    /**
     * Field group.
     *
     * @return string
     */
    public function group(): string
    {
        return 'basic';
    }

    /**
     * Dashicon.
     *
     * @return string
     */
    public function icon(): string
    {
        return 'dashicons-editor-textcolor';
    }

    /**
     * Default values.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'text',

            'label' => __('Text Field', 'formvexa-form-builder'),

            'name' => 'text_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => '',

            'default_value' => '',

            'required' => false,
        ];
    }

    /**
     * Builder settings.
     *
     * @return array
     */
    public function settings(): array
    {
        return [

            'General' => [

                Setting::text(
                    'label',
                    __('Label', 'formvexa-form-builder')
                ),

                Setting::text(
                    'name',
                    __('Name', 'formvexa-form-builder')
                ),

                Setting::text(
                    'class',
                    __('CSS Class', 'formvexa-form-builder')
                ),

                Setting::text(
                    'placeholder',
                    __('Placeholder', 'formvexa-form-builder')
                ),

                Setting::text(
                    'default_value',
                    __('Default Value', 'formvexa-form-builder')
                ),

                Setting::checkbox(
                    'required',
                    __('Required', 'formvexa-form-builder')
                ),
            ],
        ];
    }

    /**
     * Validate value.
     */
    public function validate($value = null): bool
    {
        $value = trim((string) $value);

        /*
        |--------------------------------------------------------------------------
        | Required
        |--------------------------------------------------------------------------
        */

        if (
            $this->required() &&
            $value === ''
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Optional Empty
        |--------------------------------------------------------------------------
        */

        if ($value === '') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Text Validation
        |--------------------------------------------------------------------------
        */

        if (!is_string($value)) {
            return false;
        }

        if (strlen($value) > 255) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize value.
     */
    public function sanitize($value)
    {
        return sanitize_text_field(
            trim((string) $value)
        );
    }

    /**
     * Render frontend HTML.
     *
     * @return string
     */
    public function render(): string
    {
        $required = $this->required()
            ? 'required'
            : '';

        return sprintf(
            '<input type="text" id="%1$s" name="%2$s" class="%3$s" placeholder="%4$s" value="%5$s" data-label="%6$s" %7$s />',
            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('placeholder')),
            esc_attr($this->value('default_value')),
            esc_attr($this->value('label')),
            $this->required() ? 'required="required"' : ''
        );
    }
}