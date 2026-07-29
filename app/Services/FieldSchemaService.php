<?php
/**
 * Field Schema Service.
 *
 * @package FormNova
 */

namespace FormNova\Services;

defined('ABSPATH') || exit;

use FormNova\Fields\Registry;
use FormNova\Contracts\FieldInterface;

final class FieldSchemaService
{
    /**
     * Get all field schemas.
     *
     * @return array
     */
    public function all(): array
    {
        $schemas = [];

        foreach (Registry::all() as $field) {

            $schemas[$field->type()] = $this->make($field);
        }

        return $schemas;
    }

    /**
     * Get single field schema.
     *
     * @param string $type
     *
     * @return array|null
     */
    public function get(string $type): ?array
    {
        $field = Registry::get($type);

        if (!$field) {
            return null;
        }

        return $this->make($field);
    }

    /**
     * Get grouped fields.
     *
     * @return array
     */
    public function grouped(): array
    {
        $groups = [];

        foreach (Registry::all() as $field) {

            $groups[$field->group()][] = $this->make($field);
        }

        ksort($groups);

        return $groups;
    }

    /**
     * Build schema.
     *
     * @param FieldInterface $field
     *
     * @return array
     */
    private function make(FieldInterface $field): array
    {   
        return [

            'type' => $field->type(),

            'title' => $field->title(),

            'group' => $field->group(),

            'icon' => $field->icon(),

            'defaults' => $field->defaults(),

            'settings' => $field->settings(),

        ];
    }
}