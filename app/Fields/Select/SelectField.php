<?php

namespace FormNova\Fields\Select;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class SelectField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'select';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Select', 'formnova-form');
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
        return 'dashicons-list-view';
    }

    /**
     * Default field values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'select',

            'label' => __('Select Field', 'formnova-form'),

            'name' => 'select_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'placeholder' => __('Select...', 'formnova-form'),

            'required' => false,

            'default_value' => '',

            'options' => [

                [
                    'label' => __('Option 1', 'formnova-form'),
                    'value' => 'option_1',
                ],

                [
                    'label' => __('Option 2', 'formnova-form'),
                    'value' => 'option_2',
                ],

            ],

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

                Setting::checkbox(
                    'required',
                    __('Required', 'formnova-form')
                ),

            ],

            'Options' => [

                Setting::options(
                    'options',
                    __('Options', 'formnova-form'),
                    [
                        'description' => __(
                            'Add, remove and reorder options.',
                            'formnova-form'
                        ),
                    ]
                ),

                Setting::text(
                    'default_value',
                    __('Default Value', 'formnova-form'),
                    [
                        'description' => __(
                            'Default option value.',
                            'formnova-form'
                        ),
                    ]
                ),

            ],

        ];
    }

    /**
     * Sanitize submitted value.
     */
    public function sanitize($value)
    {
        if (is_array($value)) {

            return array_map(
                'sanitize_text_field',
                $value
            );
        }

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
            (
                $value === '' ||
                $value === null
            )
        ) {
            return __(
                'This field is required.',
                'formnova-form'
            );
        }

        if (
            $value === '' ||
            $value === null
        ) {
            return true;
        }

        $options = $this->value('options') ?: [];

        foreach ($options as $option) {

            if (
                isset($option['value']) &&
                (string) $option['value'] === (string) $value
            ) {
                return true;
            }
        }

        return __(
            'Invalid selection.',
            'formnova-form'
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

        $html = sprintf(
            '<select
            id="%1$s"
            name="%2$s"
            class="%3$s"
            data-label="%4$s"
            %5$s>',
            esc_attr($this->value('id')),
            esc_attr($this->value('name')),
            esc_attr($this->value('class')),
            esc_attr($this->value('label')),
            $required
        );

        $placeholder = $this->value('placeholder');

        if (!empty($placeholder)) {

            $selected = empty($this->value('default_value'))
                ? 'selected'
                : '';

            $html .= sprintf(
                '<option value="" %1$s>%2$s</option>',
                $selected,
                esc_html($placeholder)
            );
        }

        $options = $this->value('options');

        if (is_array($options)) {

            foreach ($options as $option) {

                if (!is_array($option)) {
                    continue;
                }

                $value = $option['value'] ?? '';

                $label = $option['label'] ?? '';

                $selected = selected(
                    $this->value('default_value'),
                    $value,
                    false
                );

                $html .= sprintf(
                    '<option value="%1$s" %2$s>%3$s</option>',
                    esc_attr($value),
                    $selected,
                    esc_html($label)
                );
            }
        }

        $html .= '</select>';

        return $html;
    }
}