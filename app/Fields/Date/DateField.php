<?php

namespace FormNova\Fields\Date;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class DateField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'date';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Date', 'formnova-form');
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
        return 'dashicons-calendar-alt';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'date',

            'label' => __('Date', 'formnova-form'),

            'name' => 'date_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => '',

            'default_value' => '',

            'required' => false,

            'min' => '',

            'max' => '',
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
                    __('Label', 'formnova-form')
                ),

                Setting::text(
                    'name',
                    __('Name', 'formnova-form')
                ),

                Setting::text(
                    'class',
                    __('CSS Class', 'formnova-form')
                ),

                Setting::text(
                    'placeholder',
                    __('Placeholder', 'formnova-form')
                ),

                Setting::date(
                    'default_value',
                    __('Default Date', 'formnova-form')
                ),

                Setting::checkbox(
                    'required',
                    __('Required', 'formnova-form')
                ),
            ],

            'Validation' => [

                Setting::date(
                    'min',
                    __('Minimum Date', 'formnova-form')
                ),

                Setting::date(
                    'max',
                    __('Maximum Date', 'formnova-form')
                ),
            ],
        ];
    }

    public function sanitize($value)
    {
        return sanitize_text_field(
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
            return __('Date is required.', 'formnova-form');
        }

        if ($value === '') {
            return true;
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return __('Invalid date.', 'formnova-form');
        }

        $min = $this->value('min');

        if (
            !empty($min) &&
            strtotime($value) < strtotime($min)
        ) {
            return sprintf(
                /* translators: %s: Date in YYYY-MM-DD format. */
                __('Date must be after %s.', 'formnova-form'),
                $min
            );
        }

        $max = $this->value('max');

        if (
            !empty($max) &&
            strtotime($value) > strtotime($max)
        ) {
            return sprintf(
                /* translators: %s: Date in YYYY-MM-DD format. */
                __('Date must be before %s.', 'formnova-form'),
                $max
            );
        }

        return true;
    }

    /**
     * Render frontend HTML.
     */
    public function render(): string
    {
        $required = $this->required()
            ? 'required'
            : '';

        return sprintf(
            '<input
                type="date"
                id="%1$s"
                name="%2$s"
                class="%3$s"
                placeholder="%4$s"
                value="%5$s"
                data-label="%6$s"
                min="%7$s"
                max="%8$s"
                %9$s
            />',

            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('placeholder')),
            esc_attr($this->value('default_value')),
            esc_attr($this->value('label')),
            esc_attr($this->value('min')),
            esc_attr($this->value('max')),
            $required
        );
    }
}