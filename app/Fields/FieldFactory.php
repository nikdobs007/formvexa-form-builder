<?php

namespace formvexa\Fields;

defined('ABSPATH') || exit;

use formvexa\Contracts\FieldInterface;

final class FieldFactory
{
    /**
     * Create field instance.
     *
     * @param string $type Field type.
     * @param array  $data Field data.
     *
     * @return \formvexa\Contracts\FieldInterface
     *
     * @throws \RuntimeException When the field type is not registered.
     */
    public static function make(
        string $type,
        array $data = []
    ): FieldInterface {

        $field = Registry::get($type);

        if (!$field) {
            throw new \RuntimeException(
                sprintf(
                    'Field "%s" is not registered.',
                    sanitize_key($type)
                )
            );
        }

        $object = clone $field;

        if (method_exists($object, 'fill')) {
            $object->fill($data);
        }

        return $object;
    }
}