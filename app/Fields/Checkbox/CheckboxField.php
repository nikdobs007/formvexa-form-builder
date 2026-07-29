<?php

namespace FormNova\Fields\Checkbox;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class CheckboxField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'checkbox';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Checkbox', 'formnova-form');
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
        return 'dashicons-yes-alt';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'checkbox',

            'label' => __('Checkbox', 'formnova-form'),

            'name' => 'checkbox_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'required' => false,

            'options' => [
                [
                    'label' => 'Option 1',
                    'value' => 'option_1',
                ],
                [
                    'label' => 'Option 2',
                    'value' => 'option_2',
                ],
            ],

            'default_value' => []

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

                Setting::checkbox(
                    'required',
                    __('Required', 'formnova-form')
                ),

            ],

            'Options' => [

                Setting::options(
                    'options',
                    __('Options', 'formnova-form')
                ),

            ],

        ];
    }

    /**
     * Sanitize value.
     */
    public function sanitize($value)
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(
                    'sanitize_text_field',
                    $value
                )
            )
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
            empty($value)
        ) {
            return __('Please select at least one option.', 'formnova-form');
        }

        if (empty($value)) {
            return true;
        }

        $allowed = [];

        foreach (($this->value('options') ?: []) as $option) {

            if (
                is_array($option) &&
                isset($option['value'])
            ) {
                $allowed[] = (string) $option['value'];
            }
        }

        foreach ($value as $selected) {

            if (
                !in_array(
                    (string) $selected,
                    $allowed,
                    true
                )
            ) {
                return __('Invalid option selected.', 'formnova-form');
            }
        }

        return true;
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

        $html = '';

        foreach ($field['options'] as $option) {

            $label = is_array($option)
                ? ($option['label'] ?? '')
                : $option;

            $html .= sprintf(
                '<label>
                <input type="checkbox" disabled />
                %s
            </label><br>',
                esc_html($label)
            );
        }

        return $html;
    }

    /**
     * Frontend render.
     */
    public function render(): string
    {
        $html = '';

        foreach (($this->value('options') ?: []) as $option) {

            $label = is_array($option)
                ? ($option['label'] ?? '')
                : $option;

            $value = is_array($option)
                ? ($option['value'] ?? '')
                : $option;

            $checked = in_array(
                $value,
                (array) $this->value('default_value'),
                true
            ) ? 'checked' : '';

            $html .= sprintf(
                '<label class="formnova-checkbox">
                    <input
                        type="checkbox"
                        name="%1$s"
                        value="%2$s"
                        data-label="%6$s"
                        class="%3$s"
                        %4$s
                        %7$s
                    />
                    %5$s
                </label>',

                esc_attr($this->value('name')),               // %1$s
                esc_attr($value),                             // %2$s
                esc_attr($this->value('class')),              // %3$s
                $checked,                                     // %4$s
                esc_html($label),                             // %5$s
                esc_attr($label),                             // %6$s
                $this->required() ? 'required' : ''           // %7$s
            );
        }

        return $html;
    }
}