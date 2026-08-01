<?php

namespace formvexa\Fields\Paragraph;

defined('ABSPATH') || exit;

use formvexa\Fields\BaseField;
use formvexa\Fields\Schema\Setting;

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
        return __('Paragraph', 'formvexa-form-builder');
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

            'label' => __('Paragraph', 'formvexa-form-builder'),

            'content' => __(
                'This is a paragraph.',
                'formvexa-form-builder'
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
                    __('Label', 'formvexa-form-builder')
                ),

                Setting::textarea(
                    'content',
                    __('Content', 'formvexa-form-builder')
                ),

                Setting::text(
                    'class',
                    __('CSS Class', 'formvexa-form-builder')
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
            'formvexa-paragraph ' .
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