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
     * @return FieldInterface
     *
     * @throws \RuntimeException When the field type is not registered.
     */
    public static function make(
        string $type,
        array $data = []
    ): FieldInterface {

        $class = Registry::get($type);

        if (!$class) {
            throw new \RuntimeException(
                sprintf(
                    'Field "%s" is not registered.',
                    sanitize_key($type)
                )
            );
        }

        return new $class($data);
    }
}