<?php
/**
 * Abstract field.
 *
 * @package formvexa
 */

namespace formvexa\Fields;

defined('ABSPATH') || exit;

use formvexa\Contracts\FieldInterface;

abstract class AbstractField implements FieldInterface
{
    public function group(): string
    {
        return 'basic';
    }

    public function icon(): string
    {
        return 'dashicons-feedback';
    }

    public function defaults(): array
    {
        return [
            'id' => '',
            'type' => '',
            'label' => '',
            'name' => '',
            'class' => '',
            'placeholder' => '',
            'required' => false,
            'description' => '',
        ];
    }

    public function properties(): array
    {
        return [];
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }

    public function validate($value): bool
    {
        return true;
    }
}