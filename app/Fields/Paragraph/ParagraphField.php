<?php

namespace FormNova\Fields\Paragraph;

defined('ABSPATH') || exit;

use FormNova\Fields\BaseField;
use FormNova\Fields\Schema\Setting;

final class ParagraphField extends BaseField
{
    /**
     * Field type.
     */
    public function type(): string
    {
        return 'paragraph';
    }

    /**
     * Field title.
     */
    public function title(): string
    {
        return __('Paragraph', 'formnova-form-builder');
    }

    /**
     * Builder group.
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
        return 'dashicons-editor-paragraph';
    }

    /**
     * Default values.
     */
    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'paragraph',

            'label' => __('Paragraph', 'formnova-form-builder'),

            'content' => __(
                'This is a paragraph.',
                'formnova-form-builder'
            ),

            'class' => '',

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

                Setting::textarea(
                    'content',
                    __('Content', 'formnova-form-builder')
                ),

                Setting::text(
                    'class',
                    __('CSS Class', 'formnova-form-builder')
                ),

            ],

        ];
    }
    
    public function sanitize($value)
    {
        return '';
    }

    /**
     * Validation.
     *
     * Paragraph is not a user input.
     */
    public function validate($value = null): bool
    {
        return true;
    }

    /**
     * Render frontend.
     */
    public function render(): string
    {
        $class = trim(
            'formnova-paragraph ' .
            $this->value('class')
        );

        return sprintf(
            '<div class="%1$s">%2$s</div>',
            esc_attr($class),
            wp_kses_post(
                wpautop(
                    $this->value('content')
                )
            )
        );
    }
}