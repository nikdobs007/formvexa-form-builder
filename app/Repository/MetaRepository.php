<?php
/**
 * Meta repository.
 *
 * @package FormNova
 */

namespace FormNova\Repository;

defined('ABSPATH') || exit;

use FormNova\Contracts\RepositoryInterface;
use wpdb;
use FormNova\Helpers\DatabaseHelper;

/**
 * Meta repository.
 *
 * @package FormNova
 *
 * phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
 * phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_value
 */
final class MetaRepository implements RepositoryInterface
{
    /**
     * Database instance.
     *
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Meta table.
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
        $this->table = $wpdb->prefix . 'ndfb_form_meta';
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
     * Find meta by ID.
     *
     * @param int $id Meta ID.
     *
     * @return object|null
     */
    public function find(int $id): ?object
    {
        global $wpdb;

        $result = DatabaseHelper::get_row(
            $wpdb->prepare(
                "SELECT
                id,
                form_id,
                meta_key,
                meta_value
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE id = %d
            LIMIT 1",
                absint($id)
            )
        );

        if (!($result instanceof \stdClass)) {
            return null;
        }

        $json = json_decode($result->meta_value, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            $result->meta_value = $json;
        }

        return $result;
    }

    /**
     * Find meta by form/key.
     *
     * @param int    $form_id Form ID.
     * @param string $key     Meta key.
     *
     * @return object|null
     */
    public function find_by_key(
        int $form_id,
        string $key
    ): ?object {
        global $wpdb;

        $row = DatabaseHelper::get_row(
            $wpdb->prepare(
                "SELECT
                id,
                form_id,
                meta_key,
                meta_value
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE form_id = %d
            AND meta_key = %s
            LIMIT 1",
                absint($form_id),
                sanitize_key($key)
            )
        );

        if (!($row instanceof \stdClass)) {
            return null;
        }

        $json = json_decode($row->meta_value, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            $row->meta_value = $json;
        }

        return $row;
    }

    /**
     * Get meta value.
     *
     * @param int    $form_id Form ID.
     * @param string $key Meta key.
     *
     * @return mixed
     */
    public function get(
        int $form_id,
        string $key
    ) {

        $meta = $this->find_by_key(
            $form_id,
            $key
        );

        return $meta ? $meta->meta_value : null;
    }

    /**
     * Get all meta.
     *
     * @param array $args Query args.
     *
     * @return array
     */
    /**
     * Get all meta.
     *
     * @param array $args Query args.
     *
     * @return array
     */
    public function all(array $args = []): array
    {
        global $wpdb;

        $rows = [];

        if (!empty($args['form_id']) && !empty($args['meta_key'])) {

            $rows = DatabaseHelper::get_results(
                $wpdb->prepare(
                    "SELECT
                    id,
                    form_id,
                    meta_key,
                    meta_value
                FROM {$wpdb->prefix}ndfb_form_meta
                WHERE form_id = %d
                AND meta_key = %s
                ORDER BY id ASC",
                    absint($args['form_id']),
                    sanitize_key($args['meta_key'])
                ),
                ARRAY_A
            );

        } elseif (!empty($args['form_id'])) {

            $rows = DatabaseHelper::get_results(
                $wpdb->prepare(
                    "SELECT
                    id,
                    form_id,
                    meta_key,
                    meta_value
                FROM {$wpdb->prefix}ndfb_form_meta
                WHERE form_id = %d
                ORDER BY id ASC",
                    absint($args['form_id'])
                ),
                ARRAY_A
            );

        } elseif (!empty($args['meta_key'])) {

            $rows = DatabaseHelper::get_results(
                $wpdb->prepare(
                    "SELECT
                    id,
                    form_id,
                    meta_key,
                    meta_value
                FROM {$wpdb->prefix}ndfb_form_meta
                WHERE meta_key = %s
                ORDER BY id ASC",
                    sanitize_key($args['meta_key'])
                ),
                ARRAY_A
            );

        } else {

            $rows = DatabaseHelper::get_results(
                "SELECT
                id,
                form_id,
                meta_key,
                meta_value
            FROM {$wpdb->prefix}ndfb_form_meta
            ORDER BY id ASC",
                ARRAY_A
            );
        }

        foreach ($rows as &$row) {

            $json = json_decode(
                $row['meta_value'],
                true
            );

            if (JSON_ERROR_NONE === json_last_error()) {
                $row['meta_value'] = $json;
            }
        }

        unset($row);

        return $rows;
    }

    /**
     * Create meta.
     *
     * @param array $data Meta data.
     *
     * @return int
     */
    public function create(array $data): int
    {
        $result = $this->wpdb->insert(
            $this->table,
            [
                'form_id' => absint($data['form_id']),
                'meta_key' => sanitize_key($data['meta_key']),
                'meta_value' => wp_json_encode($data['meta_value']),
            ],
            [
                '%d',
                '%s',
                '%s',
            ]
        );

        if (false === $result) {
            return 0;
        }

        return (int) $this->wpdb->insert_id;
    }

    /**
     * Update meta.
     *
     * @param int   $id   Meta ID.
     * @param array $data Meta data.
     *
     * @return bool
     */
    public function update(
        int $id,
        array $data
    ): bool {

        $update = [];

        $formats = [];

        if (isset($data['meta_key'])) {
            $update['meta_key'] = sanitize_key($data['meta_key']);
            $formats[] = '%s';
        }

        if (array_key_exists('meta_value', $data)) {
            $update['meta_value'] = wp_json_encode(
                $data['meta_value']
            );
            $formats[] = '%s';
        }

        if (empty($update)) {
            return false;
        }

        $result = $this->wpdb->update(
            $this->table,
            $update,
            [
                'id' => absint($id),
            ],
            $formats,
            [
                '%d',
            ]
        );

        return false !== $result;
    }

    /**
     * Insert or update meta.
     *
     * @param int    $form_id Form ID.
     * @param string $key     Meta key.
     * @param mixed  $value   Meta value.
     *
     * @return bool
     */
    public function upsert(
        int $form_id,
        string $key,
        $value
    ): bool {

        $meta = $this->find_by_key(
            $form_id,
            $key
        );

        if ($meta) {

            return $this->update(
                (int) $meta->id,
                [
                    'meta_value' => $value,
                ]
            );
        }

        return $this->create(
            [
                'form_id' => $form_id,
                'meta_key' => $key,
                'meta_value' => $value,
            ]
        ) > 0;
    }

    /**
     * Delete meta.
     *
     * @param int $id Meta ID.
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        $result = $this->wpdb->delete(
            $this->table,
            [
                'id' => absint($id),
            ],
            [
                '%d',
            ]
        );

        return false !== $result;
    }

    /**
     * Bulk delete meta.
     *
     * @param array $ids Meta IDs.
     *
     * @return int
     */
    public function bulk_delete(array $ids): int
    {
        global $wpdb;

        $ids = array_filter(
            array_map(
                'absint',
                $ids
            )
        );

        if (empty($ids)) {
            return 0;
        }

        $result = DatabaseHelper::query(
            $wpdb->prepare(
                "DELETE
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE id IN (" . implode(
                    ',',
                    array_fill(
                        0,
                        count($ids),
                        '%d'
                    )
                ) . ")",
                ...$ids
            )
        );

        return false === $result ? 0 : (int) $result;
    }

    /**
     * Get paginated meta.
     *
     * @param int    $page     Current page.
     * @param int    $per_page Records per page.
     * @param string $orderby  Order by column.
     * @param string $order    ASC|DESC.
     *
     * @return array
     */
    public function paginate(
        int $page = 1,
        int $per_page = 20,
        string $orderby = 'id',
        string $order = 'DESC'
    ): array {

        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $orderby = in_array(
            $orderby,
            ['id', 'form_id', 'meta_key'],
            true
        ) ? $orderby : 'id';

        $order = ('ASC' === strtoupper($order))
            ? 'ASC'
            : 'DESC';

        if ('id' === $orderby) {

            if ('ASC' === $order) {

                $rows = DatabaseHelper::get_results(
                    $wpdb->prepare(
                        "SELECT
                        id,
                        form_id,
                        meta_key,
                        meta_value
                    FROM {$wpdb->prefix}ndfb_form_meta
                    ORDER BY id ASC
                    LIMIT %d
                    OFFSET %d",
                        absint($per_page),
                        absint($offset)
                    ),
                    ARRAY_A
                );

            } else {

                $rows = DatabaseHelper::get_results(
                    $wpdb->prepare(
                        "SELECT
                        id,
                        form_id,
                        meta_key,
                        meta_value
                    FROM {$wpdb->prefix}ndfb_form_meta
                    ORDER BY id DESC
                    LIMIT %d
                    OFFSET %d",
                        absint($per_page),
                        absint($offset)
                    ),
                    ARRAY_A
                );
            }

        } elseif ('form_id' === $orderby) {

            if ('ASC' === $order) {

                $rows = DatabaseHelper::get_results(
                    $wpdb->prepare(
                        "SELECT
                        id,
                        form_id,
                        meta_key,
                        meta_value
                    FROM {$wpdb->prefix}ndfb_form_meta
                    ORDER BY form_id ASC
                    LIMIT %d
                    OFFSET %d",
                        absint($per_page),
                        absint($offset)
                    ),
                    ARRAY_A
                );

            } else {

                $rows = DatabaseHelper::get_results(
                    $wpdb->prepare(
                        "SELECT
                        id,
                        form_id,
                        meta_key,
                        meta_value
                    FROM {$wpdb->prefix}ndfb_form_meta
                    ORDER BY form_id DESC
                    LIMIT %d
                    OFFSET %d",
                        absint($per_page),
                        absint($offset)
                    ),
                    ARRAY_A
                );
            }

        } else {
            if ('ASC' === $order) {

                $rows = DatabaseHelper::get_results(
                    $wpdb->prepare(
                        "SELECT
                        id,
                        form_id,
                        meta_key,
                        meta_value
                    FROM {$wpdb->prefix}ndfb_form_meta
                    ORDER BY meta_key ASC
                    LIMIT %d
                    OFFSET %d",
                        absint($per_page),
                        absint($offset)
                    ),
                    ARRAY_A
                );

            } else {

                $rows = DatabaseHelper::get_results(
                    $wpdb->prepare(
                        "SELECT
                        id,
                        form_id,
                        meta_key,
                        meta_value
                    FROM {$wpdb->prefix}ndfb_form_meta
                    ORDER BY meta_key DESC
                    LIMIT %d
                    OFFSET %d",
                        absint($per_page),
                        absint($offset)
                    ),
                    ARRAY_A
                );
            }
        }

        foreach ($rows as &$row) {

            $json = json_decode(
                $row['meta_value'],
                true
            );

            if (JSON_ERROR_NONE === json_last_error()) {
                $row['meta_value'] = $json;
            }
        }

        unset($row);

        return $rows;
    }

    /**
     * Search meta.
     *
     * @param string $keyword Search keyword.
     * @param int    $page    Current page.
     * @param int    $per_page Records per page.
     *
     * @return array
     */
    public function search(
        string $keyword,
        int $page = 1,
        int $per_page = 20
    ): array {

        global $wpdb;

        $offset = ($page - 1) * $per_page;
        $like = '%' . $wpdb->esc_like($keyword) . '%';

        $rows = DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                id,
                form_id,
                meta_key,
                meta_value
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE meta_key LIKE %s
            ORDER BY id DESC
            LIMIT %d OFFSET %d",
                $like,
                absint($per_page),
                absint($offset)
            ),
            ARRAY_A
        );

        foreach ($rows as &$row) {

            $json = json_decode(
                $row['meta_value'],
                true
            );

            if (JSON_ERROR_NONE === json_last_error()) {
                $row['meta_value'] = $json;
            }
        }

        unset($row);

        return $rows;
    }

    /**
     * Count meta records.
     *
     * @return int
     */
    public function count(): int
    {
        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE 1 = %d",
                1
            )
        );
    }

    /**
     * Check meta exists.
     *
     * @param int $id Meta ID.
     *
     * @return bool
     */
    public function exists(int $id): bool
    {
        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE id = %d",
                absint($id)
            )
        ) > 0;
    }

    /**
     * Delete all meta for a form.
     *
     * @param int $form_id Form ID.
     *
     * @return bool
     */
    public function delete_by_form(int $form_id): bool
    {
        $result = $this->wpdb->delete(
            $this->table,
            [
                'form_id' => absint($form_id),
            ],
            [
                '%d',
            ]
        );

        return false !== $result;
    }

    /**
     * Get latest meta record.
     *
     * @return object|null
     */
    public function latest(): ?object
    {
        global $wpdb;

        $result = DatabaseHelper::get_row(
            "SELECT
                id,
                form_id,
                meta_key,
                meta_value
            FROM {$wpdb->prefix}ndfb_form_meta
            ORDER BY id DESC
            LIMIT 1"
        );

        if (!($result instanceof \stdClass)) {
            return null;
        }

        $json = json_decode($result->meta_value, true);

        if (JSON_ERROR_NONE === json_last_error()) {
            $result->meta_value = $json;
        }

        return $result;
    }

    /**
     * Dropdown options.
     *
     * @return array
     */
    public function dropdown(): array
    {
        global $wpdb;

        $rows = DatabaseHelper::get_results(
            "SELECT
                id,
                meta_key
            FROM {$wpdb->prefix}ndfb_form_meta
            ORDER BY meta_key ASC"
        );

        $options = [];

        foreach ($rows as $row) {
            $options[(int) $row->id] = $row->meta_key;
        }

        return $options;
    }

    /**
     * Truncate table.
     *
     * @return bool
     */
    public function truncate(): bool
    {
        return false !== DatabaseHelper::query(
            "TRUNCATE TABLE {$this->table}"
        );
    }

    /**
     * Repository name.
     *
     * @return string
     */
    public function name(): string
    {
        return 'meta';
    }

    /**
     * Total pages.
     *
     * @param int $per_page Records per page.
     *
     * @return int
     */
    public function total_pages(int $per_page = 20): int
    {
        $per_page = max(1, absint($per_page));

        return (int) ceil(
            $this->count() / $per_page
        );
    }

    public function get_all_by_entry(int $entry_id): array
    {
        global $wpdb;

        return DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                field_key,
                field_value
            FROM {$wpdb->prefix}ndfb_form_meta
            WHERE entry_id = %d",
                absint($entry_id)
            )
        );
    }
}
// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key
// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_value