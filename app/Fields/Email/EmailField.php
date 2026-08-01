<?php

namespace formvexa\Fields\Email;

defined('ABSPATH') || exit;

use formvexa\Fields\BaseField;
use formvexa\Fields\Schema\Setting;

final class EmailField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'email';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Email', 'formvexa-form-builder');
    }

    /**
     * Field group.
     */
    public function group(): string
    {
        return 'basic';
    }

    /**
     * Dashicon.
     */
    public function icon(): string
    {
        return 'dashicons-email';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'email',

            'label' => __('Email', 'formvexa-form-builder'),

            'name' => 'email_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => '',

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
     * Sanitize submitted value.
     */
    public function sanitize($value)
    {
        return sanitize_email(
            (string) $value
        );
    }

    /**
     * Validate submitted value.
     */
    public function validate($value = null): bool
    {
        $value = $this->sanitize($value);

        if (
            $this->required() &&
            $value === ''
        ) {
            return __('Email is required.', 'formvexa-form-builder');
        }

        if ($value === '') {
            return true;
        }

        if (!is_email($value)) {
            return __('Please enter a valid email address.', 'formvexa-form-builder');
        }

        return true;
    }

    /**
     * Render frontend.
     */
    public function render(): string
    {
        $required = $this->required()
            ? 'required'
            : '';

        return sprintf(
            '<input
                type="email"
                id="%1$s"
                name="%2$s"
                class="%3$s"
                placeholder="%4$s"
                value="%5$s"
                data-label="%6$s"
                %7$s
            />',
            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('placeholder')),
            esc_attr($this->value('default_value')),
            esc_attr($this->value('label')),
            $required
        );
    }
}