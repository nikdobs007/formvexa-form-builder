<?php
/**
 * Entry Meta Repository.
 *
 * @package formvexa
 */

namespace formvexa\Repository;

defined('ABSPATH') || exit;

use wpdb;
use formvexa\Helpers\DatabaseHelper;

final class EntryMetaRepository
{
    /**
     * Database instance.
     *
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Table name.
     *
     * @var string
     */
    private string $table;

    /**
     * Constructor.
     *
     * @param wpdb $wpdb Database instance.
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'ndfb_entry_meta';
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function get_table(): string
    {
        return $this->table;
    }

    /**
     * Create meta row.
     *
     * @param int    $entry_id
     * @param string $field_key
     * @param mixed  $field_value
     *
     * @return bool
     */
    public function create(
        int $entry_id,
        string $field_key,
        $field_value
    ): bool {

        if (is_array($field_value)) {
            $field_value = wp_json_encode($field_value);
        }

        $result = $this->wpdb->insert(
            $this->table,
            [
                'entry_id' => absint($entry_id),
                'field_key' => sanitize_key($field_key),
                'field_value' => (string) $field_value,
            ],
            [
                '%d',
                '%s',
                '%s',
            ]
        );

        return false !== $result;
    }

    /**
     * Update meta row.
     *
     * @param int    $entry_id
     * @param string $field_key
     * @param mixed  $field_value
     *
     * @return bool
     */
    public function update(
        int $entry_id,
        string $field_key,
        $field_value
    ): bool {

        if (is_array($field_value)) {
            $field_value = wp_json_encode($field_value);
        }

        $result = $this->wpdb->update(
            $this->table,
            [
                'field_value' => (string) $field_value,
            ],
            [
                'entry_id' => absint($entry_id),
                'field_key' => sanitize_key($field_key),
            ],
            [
                '%s',
            ],
            [
                '%d',
                '%s',
            ]
        );

        return false !== $result;
    }

    /**
     * Insert or update meta.
     *
     * @param int    $entry_id
     * @param string $field_key
     * @param mixed  $field_value
     *
     * @return bool
     */
    public function upsert(
        int $entry_id,
        string $field_key,
        $field_value
    ): bool {

        if ($this->exists($entry_id, $field_key)) {

            return $this->update(
                $entry_id,
                $field_key,
                $field_value
            );
        }

        return $this->create(
            $entry_id,
            $field_key,
            $field_value
        );
    }

    /**
     * Find one meta row.
     *
     * @param int    $entry_id
     * @param string $field_key
     *
     * @return object|null
     */
    public function find(
        int $entry_id,
        string $field_key
    ): ?object {

        global $wpdb;

        $result = DatabaseHelper::get_row(
            $wpdb->prepare(
                "SELECT
                id,
                entry_id,
                field_key,
                field_value
            FROM {$wpdb->prefix}ndfb_entry_meta
            WHERE entry_id = %d
            AND field_key = %s
            LIMIT 1",
                absint($entry_id),
                sanitize_key($field_key)
            )
        );

        if (!($result instanceof \stdClass)) {
            return null;
        }

        $json = json_decode($result->field_value, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            $result->field_value = $json;
        }

        return $result;
    }

    /**
     * Check meta exists.
     *
     * @param int    $entry_id
     * @param string $field_key
     *
     * @return bool
     */
    public function exists(
        int $entry_id,
        string $field_key
    ): bool {

        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_entry_meta
            WHERE entry_id = %d
            AND field_key = %s",
                absint($entry_id),
                sanitize_key($field_key)
            )
        ) > 0;
    }

    /**
     * Get one meta value.
     *
     * @param int    $entry_id
     * @param string $field_key
     *
     * @return mixed
     */
    public function get(
        int $entry_id,
        string $field_key
    ) {

        $meta = $this->find(
            $entry_id,
            $field_key
        );

        return $meta
            ? $meta->field_value
            : null;
    }

    /**
     * Get all meta.
     *
     * @param int $entry_id
     *
     * @return array
     */
    public function all(
        int $entry_id
    ): array {

        global $wpdb;

        $rows = DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                id,
                entry_id,
                field_key,
                field_value
            FROM {$wpdb->prefix}ndfb_entry_meta
            WHERE entry_id = %d
            ORDER BY id ASC",
                absint($entry_id)
            ),
            ARRAY_A
        );

        foreach ($rows as &$row) {

            $json = json_decode($row['field_value'], true);

            if (JSON_ERROR_NONE === json_last_error()) {
                $row['field_value'] = $json;
            }
        }

        unset($row);

        return $rows;
    }

    /**
     * Delete one meta.
     *
     * @param int    $entry_id
     * @param string $field_key
     *
     * @return bool
     */
    public function delete(
        int $entry_id,
        string $field_key
    ): bool {

        $deleted = $this->wpdb->delete(
            $this->table,
            [
                'entry_id' => absint($entry_id),
                'field_key' => sanitize_key($field_key),
            ],
            [
                '%d',
                '%s',
            ]
        );

        return false !== $deleted;
    }

    /**
     * Delete all meta for entry.
     *
     * @param int $entry_id
     *
     * @return bool
     */
    public function delete_by_entry(
        int $entry_id
    ): bool {

        $deleted = $this->wpdb->delete(
            $this->table,
            [
                'entry_id' => absint($entry_id),
            ],
            [
                '%d',
            ]
        );

        return false !== $deleted;
    }

    /**
     * Count meta rows.
     *
     * @param int $entry_id
     *
     * @return int
     */
    public function count(
        int $entry_id
    ): int {

        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_entry_meta
            WHERE entry_id = %d",
                absint($entry_id)
            )
        );
    }
}