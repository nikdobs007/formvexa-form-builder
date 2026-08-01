<?php

namespace formvexa\Fields\Registrars;

defined('ABSPATH') || exit;

use formvexa\Fields\Registry;

final class FieldRegistrar
{
    /**
     * Register all fields.
     */
    public static function register(): void
    {
        Registry::register(
            'paragraph',
            \formvexa\Fields\Paragraph\ParagraphField::class
        );

        Registry::register(
            'text',
            \formvexa\Fields\Text\TextField::class
        );

        Registry::register(
            'email',
            \formvexa\Fields\Email\EmailField::class
        );

        Registry::register(
            'phone',
            \formvexa\Fields\Phone\PhoneField::class
        );

        Registry::register(
            'textarea',
            \formvexa\Fields\Textarea\TextareaField::class
        );

        Registry::register(
            'number',
            \formvexa\Fields\Number\NumberField::class
        );

        Registry::register(
            'date',
            \formvexa\Fields\Date\DateField::class
        );

        Registry::register(
            'url',
            \formvexa\Fields\Url\UrlField::class
        );

        Registry::register(
            'file',
            \formvexa\Fields\File\FileField::class
        );

        Registry::register(
            'radio',
            \formvexa\Fields\Radio\RadioField::class
        );

        Registry::register(
            'checkbox',
            \formvexa\Fields\Checkbox\CheckboxField::class
        );

        Registry::register(
            'select',
            \formvexa\Fields\Select\SelectField::class
        );

        /**
         * Third-party fields.
         */
        do_action('formvexa_register_fields');
    }
}