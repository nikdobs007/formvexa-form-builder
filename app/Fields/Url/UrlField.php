<?php

namespace FormNova\Fields\Url;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class URLField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'url';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('URL', 'formnova-form-builder');
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
        return 'dashicons-admin-links';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'url',

            'label' => __('URL', 'formnova-form-builder'),

            'name' => 'url_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => 'https://',

            'default_value' => '',

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
                    __('Label', 'formnova-form-builder')
                ),

                Setting::text(
                    'name',
                    __('Name', 'formnova-form-builder')
                ),

                Setting::text(
                    'class',
                    __('CSS Class', 'formnova-form-builder')
                ),

                Setting::text(
                    'placeholder',
                    __('Placeholder', 'formnova-form-builder')
                ),

                Setting::text(
                    'default_value',
                    __('Default Value', 'formnova-form-builder')
                ),

                Setting::checkbox(
                    'required',
                    __('Required', 'formnova-form-builder')
                ),

            ],

        ];
    }

    /**
     * Sanitize value.
     */
    public function sanitize($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        return esc_url_raw($value);
    }

    /**
     * Validate submitted value.
     */
    /**
     * Validate value.
     */
    public function validate($value = null): bool
    {
        $value = trim((string) $value);

        /*
        |--------------------------------------------------------------------------
        | Required Validation
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
        | Empty & Optional
        |--------------------------------------------------------------------------
        */

        if ($value === '') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | URL Validation
        |--------------------------------------------------------------------------
        */

        if (
            filter_var(
                $value,
                FILTER_VALIDATE_URL
            ) === false
        ) {
            return false;
        }

        return true;
    }

    /**
     * Render frontend.
     */
    public function render(): string
    {
        return sprintf(
            '<input type="url"
            id="%1$s"
            name="%2$s"
            class="%3$s"
            placeholder="%4$s"
            value="%5$s"
            data-label="%6$s"
            %7$s />',       
            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('placeholder')),
            esc_attr($this->value('default_value')),
            esc_attr($this->value('label')),
            $this->required() ? 'required="required"' : ''
        );
    }

    /**
     * Builder preview.
     */
    public function preview(array $field = []): string
    {
        return sprintf(
            '<input
                type="url"
                placeholder="%s"
                disabled
            />',
            esc_attr(
                $field['placeholder']
                ?? $this->value('placeholder')
            )
        );
    }
}