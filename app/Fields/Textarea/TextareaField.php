<?php

namespace formvexa\Fields\Textarea;

defined('ABSPATH') || exit;

use formvexa\Fields\BaseField;
use formvexa\Fields\Schema\Setting;

final class TextareaField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'textarea';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Textarea', 'formvexa-form-builder');
    }

    /**
     * Field group.
     */
    public function group(): string
    {
        return 'basic';
    }

    /**
     * Field icon.
     */
    public function icon(): string
    {
        return 'dashicons-editor-paragraph';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'textarea',

            'label' => __('Textarea', 'formvexa-form-builder'),

            'name' => 'textarea_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => '',

            'default_value' => '',

            'rows' => 5,

            'required' => false,

        ];
    }

    /**
     * Builder settings.
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

                Setting::number(
                    'rows',
                    __('Rows', 'formvexa-form-builder'),
                    [
                        'default' => 5,
                        'min' => 2,
                        'max' => 20,
                    ]
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
        | Paragraph Validation
        |--------------------------------------------------------------------------
        */

        if (!is_string($value)) {
            return false;
        }

        if (strlen($value) > 5000) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize value.
     */
    public function sanitize($value)
    {
        return sanitize_textarea_field(
            trim((string) $value)
        );
    }

    public function render(): string
    {
        return sprintf(
            '<textarea
            id="%1$s"
            name="%2$s"
            class="%3$s"
            placeholder="%4$s"
            rows="%5$d"
            data-label="%6$s"
            %7$s>%8$s</textarea>',

            esc_attr($this->value('id')),

            esc_attr($this->value('name')),

            esc_attr($this->value('class')),

            esc_attr($this->value('placeholder')),

            absint($this->value('rows') ?: 5),

            esc_attr($this->value('label')),

            $this->required() ? 'required' : '',

            esc_textarea($this->value('default_value'))
        );
    }

    /**
     * Builder preview.
     */
    public function preview(array $field = []): string
    {
        $field = wp_parse_args(
            $field,
            $this->defaults()
        );

        return sprintf(
            '<textarea disabled placeholder="%1$s" rows="%2$d" data-label="%3$s"></textarea>',
            esc_attr($field['placeholder'] ?: $field['label']),
            absint($field['rows'] ?: 5),
            esc_attr($field['label'] ?: $field['placeholder'])
        );
    }
}