<?php

namespace FormNova\Fields;

defined('ABSPATH') || exit;

use FormNova\Contracts\FieldInterface;

abstract class BaseField implements FieldInterface
{
    /**
     * Field data.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = wp_parse_args(
            $data,
            $this->defaults()
        );
    }

    /**
     * Default values.
     *
     * @return array
     */
    public function defaults(): array
    {
        return [
            'id' => uniqid('field_'),
            'type' => '',
            'label' => '',
            'name' => '',
            'class' => '',
            'placeholder' => '',
            'required' => false,
        ];
    }

    /**
     * Get all field data.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->data;
    }

    /**
     * Get field value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function value(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Set field value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check required.
     *
     * @return bool
     */
    public function required(): bool
    {
        return !empty($this->data['required']);
    }

    /**
     * Set complete field data.
     *
     * @param array $data
     *
     * @return static
     */
    public function fill(array $data): static
    {
        $this->data = wp_parse_args(
            $data,
            $this->defaults()
        );

        return $this;
    }

    /**
     * Field ID attribute.
     *
     * @return string
     */
    public function id(): string
    {
        return sanitize_key(
            $this->value('id')
        );
    }


    /**
     * Field name attribute.
     *
     * @return string
     */
    public function name(): string
    {
        return sanitize_key(
            $this->value('name')
        );
    }


    /**
     * Field CSS class.
     *
     * @return string
     */
    public function class(): string
    {
        return sanitize_html_class(
            $this->value('class')
        );
    }


    /**
     * Field label.
     *
     * @return string
     */
    public function label(): string
    {
        return esc_html(
            $this->value('label')
        );
    }


    /**
     * Field placeholder.
     *
     * @return string
     */
    public function placeholder(): string
    {
        return esc_html(
            $this->value('placeholder')
        );
    }

    /**
     * Default validation.
     *
     * Child classes can override this.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function validate($value = null): bool
    {
        return true;
    }

    /**
     * Builder settings.
     *
     * @return array
     */
    public function settings(): array
    {
        return [];
    }

    /**
     * Builder preview.
     *
     * @param array $field
     *
     * @return string
     */
    public function preview(array $field = []): string
    {
        return $this->render();
    }

    /**
     * Sanitize submitted value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function sanitize($value)
    {
        return is_string($value)
            ? sanitize_text_field($value)
            : $value;
    }

    /**
     * Render frontend HTML.
     *
     * @return string
     */
    abstract public function render(): string;
}