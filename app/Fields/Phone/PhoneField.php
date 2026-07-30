<?php

namespace FormNova\Fields\Phone;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class PhoneField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'phone';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Phone', 'formnova-form-builder');
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
        return 'dashicons-phone';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'phone',

            'label' => __('Phone', 'formnova-form-builder'),

            'name' => 'phone_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => '',

            'default_value' => '',

            'required' => false,

            'maxlength' => 15,

            'minlength' => '',

            'pattern' => '',

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

            'Validation' => [

                Setting::number(
                    'minlength',
                    __('Minimum Length', 'formnova-form-builder')
                ),

                Setting::number(
                    'maxlength',
                    __('Maximum Length', 'formnova-form-builder')
                ),

                Setting::text(
                    'pattern',
                    __('Pattern', 'formnova-form-builder')
                ),

            ],

        ];
    }

    /**
     * Sanitize value.
     */
    public function sanitize($value)
    {
        return preg_replace(
            '/[^0-9+\-\s()]/',
            '',
            (string) $value
        );
    }

    /**
     * Validate value.
     */
    public function validate($value = null): bool
    {
        $value = $this->sanitize($value);
        
        if (
            $this->required() &&
            $value === ''
        ) {
            return __(
                'Phone number is required.',
                'formnova-form-builder'
            );
        }

        if ($value === '') {
            return true;
        }

        if (
            !preg_match(
                '/^[0-9+\-\s()]+$/',
                $value
            )
        ) {
            return __(
                'Invalid phone number.',
                'formnova-form-builder'
            );
        }

        $digits = preg_replace(
            '/\D/',
            '',
            $value
        );

        $min = absint(
            $this->value('minlength')
        );

        $max = absint(
            $this->value('maxlength')
        );

        if (
            $min &&
            strlen($digits) < $min
        ) {
            return sprintf(
                /* translators: %d: Minimum number of digits allowed. */
                __('Minimum %d digits required.', 'formnova-form-builder'),
                $min
            );
        }

        if (
            $max &&
            strlen($digits) > $max
        ) {
            return sprintf(
                /* translators: %d: Maximum number of digits allowed. */
                __('Maximum %d digits allowed.', 'formnova-form-builder'),
                $max
            );
        }

        $pattern = $this->value('pattern');

        if (
            !empty($pattern) &&
            @preg_match('/' . $pattern . '/', '') !== false
        ) {

            if (
                !preg_match(
                    '/' . $pattern . '/',
                    $value
                )
            ) {
                return __(
                    'Invalid phone number format.',
                    'formnova-form-builder'
                );
            }
        }

        return true;
    }

    /**
     * Render frontend.
     */
    public function render(): string
    {
        $required = $this->required() ? 'required' : '';

        $pattern = '';

        if (!empty($this->value('pattern'))) {
            $pattern = sprintf(
                ' pattern="%s"',
                esc_attr($this->value('pattern'))
            );
        }

        return sprintf(
            '<input
                type="tel"
                id="%1$s"
                name="%2$s"
                class="%3$s"
                placeholder="%4$s"
                value="%5$s"
                data-label="%6$s"
                minlength="%7$s"
                maxlength="%8$s"
                %9$s
                %10$s
            />',

            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('placeholder')),
            esc_attr($this->value('default_value')),
            esc_attr($this->value('label')),
            esc_attr($this->value('minlength')),
            esc_attr($this->value('maxlength')),
            $pattern,
            $required
        );
    }
}