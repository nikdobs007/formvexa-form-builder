<?php

namespace FormNova\Fields\Registrars;

defined('ABSPATH') || exit;

use FormNova\Fields\Registry;

final class FieldRegistrar
{
    /**
     * Register all fields.
     */
    public static function register(): void
    {
        Registry::register(
            'paragraph',
            \FormNova\Fields\Paragraph\ParagraphField::class
        );

        Registry::register(
            'text',
            \FormNova\Fields\Text\TextField::class
        );

        Registry::register(
            'email',
            \FormNova\Fields\Email\EmailField::class
        );

        Registry::register(
            'phone',
            \FormNova\Fields\Phone\PhoneField::class
        );

        Registry::register(
            'textarea',
            \FormNova\Fields\Textarea\TextareaField::class
        );

        Registry::register(
            'number',
            \FormNova\Fields\Number\NumberField::class
        );

        Registry::register(
            'date',
            \FormNova\Fields\Date\DateField::class
        );

        Registry::register(
            'url',
            \FormNova\Fields\Url\UrlField::class
        );

        Registry::register(
            'file',
            \FormNova\Fields\File\FileField::class
        );

        Registry::register(
            'radio',
            \FormNova\Fields\Radio\RadioField::class
        );

        Registry::register(
            'checkbox',
            \FormNova\Fields\Checkbox\CheckboxField::class
        );

        Registry::register(
            'select',
            \FormNova\Fields\Select\SelectField::class
        );

        /**
         * Third-party fields.
         */
        do_action('formnova_register_fields');
    }
}