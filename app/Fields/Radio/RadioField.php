<?php

namespace formvexa\Fields\Radio;

defined('ABSPATH') || exit;

use formvexa\Fields\BaseField;
use formvexa\Fields\Schema\Setting;

final class RadioField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'radio';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Radio', 'formvexa-form-builder');
    }

    /**
     * Field group.
     */
    public function group(): string
    {
        return 'advanced';
    }

    /**
     * Field icon.
     */
    public function icon(): string
    {
        return 'dashicons-marker';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'radio',

            'label' => __('Radio Field', 'formvexa-form-builder'),

            'name' => 'radio_' . strtolower(wp_generate_password(6, false)),

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

            'default_value' => '',
        ];
    }

    /**
     * Builder settings.
     */
    public function settings(): array
    {
        return [

            [
                'title' => __('General', 'formvexa-form-builder'),

                'fields' => [

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

                    Setting::checkbox(
                        'required',
                        __('Required', 'formvexa-form-builder')
                    ),
                ],
            ],

            [
                'title' => __('Options', 'formvexa-form-builder'),

                'fields' => [

                    Setting::options(
                        'options',
                        __('Options', 'formvexa-form-builder')
                    ),

                    Setting::text(
                        'default_value',
                        __('Default Value', 'formvexa-form-builder')
                    ),
                ],
            ],

        ];
    }

    /**
     * Sanitize value.
     */
    public function sanitize($value)
    {
        return sanitize_text_field(
            (string) $value
        );
    }

    /**
     * Validate.
     */
    public function validate($value = null): bool
    {
        $value = $this->sanitize($value);

        if (
            $this->required() &&
            $value === ''
        ) {
            return __(
                'This field is required.',
                'formvexa-form-builder'
            );
        }

        if ($value === '') {
            return true;
        }

        $options = $this->value('options') ?: [];

        foreach ($options as $option) {

            if (
                isset($option['value']) &&
                (string) $option['value'] === $value
            ) {
                return true;
            }
        }

        return __(
            'Invalid option selected.',
            'formvexa-form-builder'
        );
    }

    /**
     * Render frontend.
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

            $checked = checked(
                $value,
                $this->value('default_value'),
                false
            );

            $html .= sprintf(
                '<label class="formvexa-radio">
                    <input
                        type="radio"
                        id="%1$s_%2$s"
                        name="%3$s"
                        class="%4$s"
                        value="%5$s"
                        data-label="%6$s"
                        %7$s
                        %8$s
                    />
                    %9$s
                </label>',
                esc_attr($this->value('id')),
                sanitize_title($value),
                esc_attr($this->value('name')),
                esc_attr($this->value('class')),
                esc_attr($value),
                esc_attr($this->value('label')),
                $checked,
                $this->required() ? 'required' : '',
                esc_html($label)
            );
        }

        return $html;
    }
}