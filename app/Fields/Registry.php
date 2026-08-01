<?php

namespace formvexa\Fields;

defined('ABSPATH') || exit;

final class Registry
{
    /**
     * Registered fields.
     *
     * @var array
     */
    private static array $fields = [];

    /**
     * Clear registered fields.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$fields = [];
    }

    /**
     * Register field.
     *
     * @param \formvexa\Contracts\FieldInterface $field Field instance.
     *
     * @return void
     */
    public static function register(
        \formvexa\Contracts\FieldInterface $field
    ): void {

        self::$fields[$field->type()] = $field;
    }

    /**
     * Has field.
     *
     * @param string $type
     *
     * @return bool
     */
    public static function has(string $type): bool
    {
        return isset(self::$fields[$type]);
    }

    /**
     * Get class.
     *
     * @param string $type
     *
     * @return string|null
     */
    public static function get(string $type): ?\formvexa\Contracts\FieldInterface
    {
        return self::$fields[$type] ?? null;
    }

    /**
     * All fields.
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$fields;
    }

    /**
     * Create field instance.
     *
     * @param array $field
     *
     * @return \formvexa\Contracts\FieldInterface|null
     */
    public static function make(
        array $field
    ): ?\formvexa\Contracts\FieldInterface {

        if (
            empty($field['type'])
        ) {
            return null;
        }

        if (
            !isset(
            self::$fields[
                $field['type']
            ]
        )
        ) {
            return null;
        }

        $object = clone self::$fields[
            $field['type']
        ];

        if (
            method_exists(
                $object,
                'fill'
            )
        ) {

            $object->fill(
                $field
            );

        }

        return $object;

    }
}