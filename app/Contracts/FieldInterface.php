<?php
/**
 * Field contract.
 *
 * @package formvexa
 */

namespace formvexa\Contracts;

defined('ABSPATH') || exit;

interface FieldInterface
{
    public function type(): string;

    public function title(): string;

    public function group(): string;

    public function icon(): string;

    public function defaults(): array;

    public function settings(): array;

    public function preview(array $field = []): string;

    public function render(): string;

    public function sanitize($value);

    public function validate($value = null): bool;

    public function fill(array $data): static;
}