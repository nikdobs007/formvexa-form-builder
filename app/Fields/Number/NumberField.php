<?php

namespace FormNova\Fields\Number;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class NumberField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'number';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Number', 'formnova-form');
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
        return 'dashicons-editor-ol';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'number',

            'label' => __('Number', 'formnova-form'),

            'name' => 'number_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => '',

            'default_value' => '',

            'min' => '',

            'max' => '',

            'step' => '1',

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

                Setting::number(
                    'default_value',
                    __('Default Value', 'formnova-form')
                ),

                Setting::checkbox(
                    'required',
                    __('Required', 'formnova-form')
                ),
            ],

            'Validation' => [

                Setting::number(
                    'min',
                    __('Minimum Value', 'formnova-form')
                ),

                Setting::number(
                    'max',
                    __('Maximum Value', 'formnova-form')
                ),

                Setting::number(
                    'step',
                    __('Step', 'formnova-form'),
                    [
                        'default' => 1,
                    ]
                ),
            ],
        ];
    }

    /**
     * Sanitize submitted value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function sanitize($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }

        return is_numeric($value)
            ? $value + 0
            : '';
    }

    /**
     * Validate submitted value.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value = null): bool
    {
        $value = $this->sanitize($value);

        if (
            $this->required() &&
            $value === ''
        ) {
            return false;
        }

        if ($value === '') {
            return true;
        }

        if (!is_numeric($value)) {
            return false;
        }

        $number = (float) $value;

        $min = $this->value('min');

        if (
            $min !== '' &&
            $number < (float) $min
        ) {
            return false;
        }

        $max = $this->value('max');

        if (
            $max !== '' &&
            $number > (float) $max
        ) {
            return false;
        }

        return true;
    }

    /**
     * Builder preview.
     *
     * @param array $field
     *
     * @return string
     */
    public function preview(array $field = []): string
    {
        return sprintf(
            '<input type="number" placeholder="%s" disabled>',
            esc_attr(
                $field['placeholder']
                ?? $field['label']
                ?? ''
            )
        );
    }

    /**
     * Render frontend.
     *
     * @return string
     */
    public function render(): string
    {
        return sprintf(
            '<input
                type="number"
                id="%1$s"
                name="%2$s"
                class="%3$s"
                placeholder="%4$s"
                value="%5$s"
                data-label="%6$s"
                min="%7$s"
                max="%8$s"
                step="%9$s"
                %10$s
            />',

            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('placeholder')),
            esc_attr($this->value('default_value')),
            esc_attr($this->value('label')),
            esc_attr($this->value('min')),
            esc_attr($this->value('max')),
            esc_attr($this->value('step')),
            $this->required() ? 'required' : ''
        );
    }
}