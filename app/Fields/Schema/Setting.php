<?php

namespace FormNova\Fields\Schema;

defined('ABSPATH') || exit;

final class Setting
{
    /**
     * Create schema.
     *
     * @param string $type
     * @param string $key
     * @param string $label
     * @param array  $args
     *
     * @return array
     */
    public static function make(
        string $type,
        string $key,
        string $label,
        array $args = []
    ): array {

        return wp_parse_args(
            $args,
            [
                'type' => $type,
                'key' => $key,
                'label' => $label,
                'default' => '',
                'placeholder' => '',
                'required' => false,
                'options' => [],
                'description' => '',
                'class' => '',
                'min' => null,
                'max' => null,
                'step' => null,
                'multiple' => false,
                'readonly' => false,
                'disabled' => false,
            ]
        );
    }

    /**
     * Text
     */
    public static function text(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'text',
            $key,
            $label,
            $args
        );
    }

    /**
     * Textarea
     */
    public static function textarea(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'textarea',
            $key,
            $label,
            $args
        );
    }

    /**
     * Number
     */
    public static function number(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'number',
            $key,
            $label,
            $args
        );
    }

    /**
     * Checkbox
     */
    public static function checkbox(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'checkbox',
            $key,
            $label,
            $args
        );
    }

    /**
     * Select
     */
    public static function select(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'select',
            $key,
            $label,
            $args
        );
    }

    /**
     * Options
     */
    public static function options(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'options',
            $key,
            $label,
            $args
        );
    }

    /**
     * File Types
     */
    public static function fileTypes(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'filetypes',
            $key,
            $label,
            $args
        );
    }

    /**
     * Mime Types
     */
    public static function mimeTypes(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'mimetypes',
            $key,
            $label,
            $args
        );
    }

    /**
     * Email
     */
    public static function email(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'email',
            $key,
            $label,
            $args
        );
    }

    /**
     * Phone
     */
    public static function phone(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'phone',
            $key,
            $label,
            $args
        );
    }

    /**
     * URL
     */
    public static function url(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'url',
            $key,
            $label,
            $args
        );
    }

    /**
     * Date
     */
    public static function date(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'date',
            $key,
            $label,
            $args
        );
    }

    /**
     * Radio Options
     */
    public static function radio(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'radio',
            $key,
            $label,
            $args
        );
    }

    /**
     * File
     */
    public static function file(
        string $key,
        string $label,
        array $args = []
    ): array {

        return self::make(
            'file',
            $key,
            $label,
            $args
        );
    }
}