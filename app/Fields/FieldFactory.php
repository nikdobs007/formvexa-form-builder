<?php

namespace FormNova\Fields;

defined('ABSPATH') || exit;

use FormNova\Contracts\FieldInterface;

final class FieldFactory
{
    /**
     * Create field instance.
     *
     * @param string $type Field type.
     * @param array  $data Field data.
     *
     * @return \FormNova\Contracts\FieldInterface
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