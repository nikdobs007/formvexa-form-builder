<?php

namespace formvexa\Fields\File;

defined('ABSPATH') || exit;

use formvexa\Fields\BaseField;
use formvexa\Fields\Schema\Setting;

final class FileField extends BaseField
{
    public function type(): string
    {
        return 'file';
    }

    public function title(): string
    {
        return __('File Upload', 'formvexa-form-builder');
    }

    public function group(): string
    {
        return 'advanced';
    }

    public function icon(): string
    {
        return 'dashicons-upload';
    }

    public function defaults(): array
    {
        return [

            'id' => uniqid('field_'),

            'type' => 'file',

            'label' => __('File Upload', 'formvexa-form-builder'),

            'name' => 'file_' . strtolower(wp_generate_password(6, false)),

            'class' => '',

            'required' => false,

            // File settings
            'allowed_extensions' => 'jpg,jpeg,png,pdf,docx',

            'allowed_mimes' => '',

            'max_size' => 5, // MB

            'multiple' => false,

            'min_files' => 0,

            'max_files' => 1,
        ];
    }

    public function settings(): array
    {
        return [

            [
                'title' => __('General', 'formvexa-form-builder'),
                'fields' => [

                    Setting::text('label', __('Label', 'formvexa-form-builder')),

                    Setting::text('name', __('Name', 'formvexa-form-builder')),

                    Setting::text('class', __('CSS Class', 'formvexa-form-builder')),

                    Setting::checkbox('required', __('Required', 'formvexa-form-builder')),
                ]
            ],

            [
                'title' => __('File Settings', 'formvexa-form-builder'),
                'fields' => [

                    Setting::text(
                        'allowed_extensions',
                        __('Allowed Extensions (comma separated)', 'formvexa-form-builder')
                    ),

                    Setting::text(
                        'allowed_mimes',
                        __('Allowed MIME Types', 'formvexa-form-builder')
                    ),

                    Setting::number(
                        'max_size',
                        __('Max Size (MB)', 'formvexa-form-builder')
                    ),

                    Setting::checkbox(
                        'multiple',
                        __('Allow Multiple Files', 'formvexa-form-builder')
                    ),

                    Setting::number(
                        'min_files',
                        __('Min Files', 'formvexa-form-builder')
                    ),

                    Setting::number(
                        'max_files',
                        __('Max Files', 'formvexa-form-builder')
                    ),
                ]
            ],
        ];
    }

    public function validate($value = null): bool
    {
        /*
        |--------------------------------------------------------------------------
        | Required
        |--------------------------------------------------------------------------
        */

        if (
            $this->required() &&
            empty($value)
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Nothing uploaded
        |--------------------------------------------------------------------------
        */

        if (empty($value)) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Uploaded filename
        |--------------------------------------------------------------------------
        */

        if (is_array($value)) {
            return true;
        }

        $extension = strtolower(
            pathinfo(
                $value,
                PATHINFO_EXTENSION
            )
        );

        $allowed = array_filter(
            array_map(
                'trim',
                explode(
                    ',',
                    strtolower(
                        (string) $this->value('allowed_extensions')
                    )
                )
            )
        );

        if (
            !empty($allowed) &&
            !in_array(
                $extension,
                $allowed,
                true
            )
        ) {
            return false;
        }

        return true;
    }

    public function sanitize($value)
    {
        if (empty($value)) {
            return '';
        }

        if (is_array($value)) {

            return array_map(
                'sanitize_text_field',
                $value
            );
        }

        return sanitize_text_field(
            $value
        );
    }

    public function preview(array $field = []): string
    {
        return '<input type="file" disabled />';
    }

    public function render(): string
    {
        $multiple = $this->value('multiple')
            ? 'multiple'
            : '';

        $required = $this->required()
            ? 'required'
            : '';

        $accept = '';

        if (!empty($this->value('allowed_extensions'))) {

            $extensions = array_map(
                static fn($ext) => '.' . trim($ext),
                explode(
                    ',',
                    $this->value('allowed_extensions')
                )
            );

            $accept = implode(
                ',',
                $extensions
            );
        }

        return sprintf(
            '<input
            type="file"
            id="%1$s"
            name="%2$s"
            class="%3$s"
            accept="%4$s"
            data-label="%5$s"
            data-max-size="%6$s"
            data-extensions="%7$s"
            %8$s
            %9$s
        />',

            esc_attr($this->value('id')),

            esc_attr($this->value('name')),

            esc_attr($this->value('class')),

            esc_attr($accept),

            esc_attr($this->value('label')),

            absint($this->value('max_size')),

            esc_attr($this->value('allowed_extensions')),

            $multiple,

            $required
        );
    }
}