<?php
/**
 * Entry Repository.
 *
 * @package formvexa
 */

namespace formvexa\Repository;

defined('ABSPATH') || exit;

use formvexa\Contracts\RepositoryInterface;
use wpdb;
use formvexa\Helpers\DatabaseHelper;

/**
 * Entry repository.
 */
final class EntryRepository implements RepositoryInterface
{

    /**
     * Database instance.
     *
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Entries table.
     *
     * @var string
     */
    private string $table;

    /**
     * Allowed columns.
     *
     * @var string[]
     */
    private const SELECT_COLUMNS =
        'id, form_id, user_id, status, ip, browser, referer, submitted_at';

    /**
     * Constructor.
     *
     * @param wpdb $wpdb WordPress database instance.
     */
    public function __construct(wpdb $wpdb)
    {

        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'ndfb_entries';
    }

    /**
     * Find entry by ID.
     *
     * @param int $id Entry ID.
     *
     * @return object|null
     */
    public function find(int $id): ?object
    {

        global $wpdb;

        $result = DatabaseHelper::get_row(
            $wpdb->prepare(
                "SELECT id, form_id, user_id, status, ip, browser, referer, submitted_at
             FROM {$wpdb->prefix}ndfb_entries
             WHERE id = %d
             LIMIT 1",
                absint($id)
            )
        );

        return ($result instanceof \stdClass) ? $result : null;
    }

    /**
     * Get entries by form.
     *
     * @param int $form_id Form ID.
     *
     * @return array<int, object>
     */
    public function by_form(int $form_id): array
    {
        global $wpdb;

        $results = DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT id, form_id, user_id, status, ip, browser, referer, submitted_at
             FROM {$wpdb->prefix}ndfb_entries
             WHERE form_id = %d
             ORDER BY submitted_at DESC",
                absint($form_id)
            )
        );

        return is_array($results) ? $results : [];
    }

    /**
     * Paginate entries.
     *
     * @param int         $page     Current page.
     * @param int         $per_page Items per page.
     * @param string|null $status   Status filter.
     *
     * @return array<int, object>
     */
    public function paginate(
        int $page = 1,
        int $per_page = 20,
        string $orderby = 'id',
        string $order = 'DESC'
    ): array {

        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $allowed = [
            'id',
            'form_id',
            'status',
            'submitted_at',
        ];

        if (!in_array($orderby, $allowed, true)) {
            $orderby = 'id';
        }

        $order = strtoupper($order) === 'ASC'
            ? 'ASC'
            : 'DESC';

        return DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT id, form_id, user_id, status, ip, browser, referer, submitted_at
            FROM {$wpdb->prefix}ndfb_entries
            ORDER BY " . esc_sql($orderby) . ' ' . esc_sql($order) . "
            LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
    }

    public function paginate_filtered(
        int $page,
        int $per_page,
        int $form_id = 0,
        string $search = ''
    ): array {

        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $entries = $wpdb->prefix . 'ndfb_entries';
        $forms = $wpdb->prefix . 'ndfb_forms';

        $where = [];
        $args = [];

        // Form Filter
        if ($form_id > 0) {

            $where[] = 'e.form_id = %d';
            $args[] = absint($form_id);
        }

        // Search
        if ($search !== '') {

            $like = '%' . $wpdb->esc_like($search) . '%';

            $where[] = '(CAST(e.id AS CHAR) LIKE %s
                    OR f.title LIKE %s)';

            $args[] = $like;
            $args[] = $like;
        }

        $sql = "
        SELECT e.*
        FROM {$entries} AS e
        LEFT JOIN {$forms} AS f
            ON f.id = e.form_id
    ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= "
        ORDER BY e.submitted_at DESC
        LIMIT %d OFFSET %d
    ";

        $args[] = absint($per_page);
        $args[] = absint($offset);

        return DatabaseHelper::get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare($sql, ...$args)
        ) ?: [];
    }

    /**
     * Count entries.
     *
     * @param string|null $status Status filter.
     *
     * @return int
     */
    public function count(
        int $form_id = 0,
        string $search = ''
    ): int {

        global $wpdb;

        if ($form_id > 0) {

            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*)
                 FROM {$wpdb->prefix}ndfb_entries
                 WHERE form_id = %d",
                    absint($form_id)
                )
            );
        }

        return (int) DatabaseHelper::get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ndfb_entries"
        );
    }


    public function count_filtered(
        int $form_id = 0,
        string $search = ''
    ): int {

        global $wpdb;

        $entries = $wpdb->prefix . 'ndfb_entries';
        $forms = $wpdb->prefix . 'ndfb_forms';

        $where = [];
        $args = [];

        // Form Filter
        if ($form_id > 0) {

            $where[] = 'e.form_id = %d';
            $args[] = absint($form_id);
        }

        // Search
        if ($search !== '') {

            $like = '%' . $wpdb->esc_like($search) . '%';

            $where[] = '(CAST(e.id AS CHAR) LIKE %s
                    OR f.title LIKE %s)';

            $args[] = $like;
            $args[] = $like;
        }

        $sql = "
        SELECT COUNT(*)
        FROM {$entries} AS e
        LEFT JOIN {$forms} AS f
            ON f.id = e.form_id
    ";

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($args)) {
            return (int) DatabaseHelper::get_var(
                // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->prepare($sql, ...$args)
            );
        }

        return (int) DatabaseHelper::get_var($sql);
    }

    /**
     * Create entry.
     *
     * @param array<string, mixed> $data Entry data.
     *
     * @return int
     */
    public function create(array $data): int
    {

        $inserted = $this->wpdb->insert(
            $this->table,
            [
                'form_id' => absint($data['form_id'] ?? 0),
                'user_id' => absint($data['user_id'] ?? get_current_user_id()),
                'status' => sanitize_key($data['status'] ?? 'completed'),
                'ip' => sanitize_text_field($data['ip'] ?? ''),
                'browser' => sanitize_textarea_field($data['browser'] ?? ''),
                'referer' => esc_url_raw($data['referer'] ?? ''),
                'submitted_at' => current_time('mysql'),
            ],
            [
                '%d',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
            ]
        );

        if (false === $inserted) {
            return 0;
        }

        return (int) $this->wpdb->insert_id;
    }

    /**
     * Update entry.
     *
     * @param int                 $id   Entry ID.
     * @param array<string,mixed> $data Entry data.
     *
     * @return bool
     */
    public function update(int $id, array $data): bool
    {

        $fields = [];
        $formats = [];

        if (array_key_exists('status', $data)) {
            $fields['status'] = sanitize_key($data['status']);
            $formats[] = '%s';
        }

        if (array_key_exists('ip', $data)) {
            $fields['ip'] = sanitize_text_field($data['ip']);
            $formats[] = '%s';
        }

        if (array_key_exists('browser', $data)) {
            $fields['browser'] = sanitize_textarea_field($data['browser']);
            $formats[] = '%s';
        }

        if (array_key_exists('referer', $data)) {
            $fields['referer'] = esc_url_raw($data['referer']);
            $formats[] = '%s';
        }

        if (array_key_exists('user_id', $data)) {
            $fields['user_id'] = absint($data['user_id']);
            $formats[] = '%d';
        }

        if (empty($fields)) {
            return false;
        }

        $updated = $this->wpdb->update(
            $this->table,
            $fields,
            [
                'id' => absint($id),
            ],
            $formats,
            [
                '%d',
            ]
        );

        return false !== $updated;
    }

    /**
     * Delete entry.
     *
     * @param int $id Entry ID.
     *
     * @return bool
     */
    public function delete(int $id): bool
    {

        $deleted = $this->wpdb->delete(
            $this->table,
            [
                'id' => absint($id),
            ],
            [
                '%d',
            ]
        );

        return false !== $deleted;
    }

    /**
     * Bulk delete entries.
     *
     * @param array<int> $ids Entry IDs.
     *
     * @return int Number of deleted rows.
     */
    public function bulk_delete(array $ids): int
    {
        global $wpdb;

        $ids = array_filter(
            array_map('absint', $ids)
        );

        if (empty($ids)) {
            return 0;
        }

        $result = DatabaseHelper::query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}ndfb_entries
            WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")",
                ...$ids
            )
        );

        return false === $result ? 0 : (int) $result;
    }

    /**
     * Search entries.
     *
     * @param string      $search   Search keyword.
     * @param int|null    $form_id  Optional form ID.
     * @param string|null $status   Optional status.
     * @param int         $page     Current page.
     * @param int         $per_page Items per page.
     *
     * @return array<int, object>
     */
    public function search(
        string $keyword,
        int $page = 1,
        int $per_page = 20
    ): array {

        global $wpdb;

        $page = max(1, $page);
        $per_page = max(1, $per_page);
        $offset = ($page - 1) * $per_page;

        if ('' !== trim($keyword)) {

            $like = '%' . $wpdb->esc_like(wp_unslash($keyword)) . '%';

            return DatabaseHelper::get_results(
                $wpdb->prepare(
                    "SELECT
                    id,
                    form_id,
                    user_id,
                    status,
                    ip,
                    browser,
                    referer,
                    submitted_at
                FROM {$wpdb->prefix}ndfb_entries
                WHERE ip LIKE %s
                   OR browser LIKE %s
                   OR referer LIKE %s
                ORDER BY submitted_at DESC
                LIMIT %d OFFSET %d",
                    $like,
                    $like,
                    $like,
                    $per_page,
                    $offset
                )
            ) ?: [];
        }

        return DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                id,
                form_id,
                user_id,
                status,
                ip,
                browser,
                referer,
                submitted_at
            FROM {$wpdb->prefix}ndfb_entries
            ORDER BY submitted_at DESC
            LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        ) ?: [];
    }

    /**
     * Determine whether an entry exists.
     *
     * @param int $id Entry ID.
     *
     * @return bool
     */
    public function exists(int $id): bool
    {
        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_entries
            WHERE id = %d",
                absint($id)
            )
        ) > 0;
    }

    /**
     * Get total search results.
     *
     * @param string      $search  Search keyword.
     * @param int|null    $form_id Optional form ID.
     * @param string|null $status  Optional status.
     *
     * @return int
     */
    public function search_count(
        string $search,
        ?int $form_id = null,
        ?string $status = null
    ): int {

        global $wpdb;

        $has_search = '' !== trim($search);
        $like = '%' . $wpdb->esc_like(wp_unslash($search)) . '%';

        // search + form + status
        if ($has_search && null !== $form_id && null !== $status) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE (ip LIKE %s OR browser LIKE %s OR referer LIKE %s)
                AND form_id = %d
                AND status = %s",
                    $like,
                    $like,
                    $like,
                    absint($form_id),
                    sanitize_key($status)
                )
            );
        }

        // search + form
        if ($has_search && null !== $form_id) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE (ip LIKE %s OR browser LIKE %s OR referer LIKE %s)
                AND form_id = %d",
                    $like,
                    $like,
                    $like,
                    absint($form_id)
                )
            );
        }

        // search + status
        if ($has_search && null !== $status) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE (ip LIKE %s OR browser LIKE %s OR referer LIKE %s)
                AND status = %s",
                    $like,
                    $like,
                    $like,
                    sanitize_key($status)
                )
            );
        }

        // form + status
        if (null !== $form_id && null !== $status) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE form_id = %d
                AND status = %s",
                    absint($form_id),
                    sanitize_key($status)
                )
            );
        }

        // search only
        if ($has_search) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE ip LIKE %s
                OR browser LIKE %s
                OR referer LIKE %s",
                    $like,
                    $like,
                    $like
                )
            );
        }

        // form only
        if (null !== $form_id) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE form_id = %d",
                    absint($form_id)
                )
            );
        }

        // status only
        if (null !== $status) {
            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_entries
                WHERE status = %s",
                    sanitize_key($status)
                )
            );
        }

        // no filters
        return (int) DatabaseHelper::get_var(
            "SELECT COUNT(id)
        FROM {$wpdb->prefix}ndfb_entries"
        );
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
     * Get allowed columns.
     *
     * @return array<int, string>
     */
    public function get_columns(): array
    {

        return array_map(
            'trim',
            explode(',', self::SELECT_COLUMNS)
        );
    }

    /**
     * Export entries by form.
     *
     * Returns flat rows for CSV export.
     *
     * @param int $form_id Form ID.
     *
     * @return array
     */
    public function export_csv(int $form_id): array
    {
        global $wpdb;

        $entries_table = $wpdb->prefix . 'ndfb_entries';
        $meta_table = $wpdb->prefix . 'ndfb_entry_meta';
        $forms_table = $wpdb->prefix . 'ndfb_forms';

        $sql = "
            SELECT
                e.id,
                e.form_id,
                f.title AS form_name,
                e.status,
                e.ip,
                e.browser,
                e.referer,
                e.submitted_at,
                m.field_key,
                m.field_value
            FROM {$entries_table} AS e
            INNER JOIN {$meta_table} AS m
                ON m.entry_id = e.id
            INNER JOIN {$forms_table} AS f
                ON f.id = e.form_id
            WHERE e.form_id = %d
            ORDER BY
                e.id DESC,
                m.id ASC
        ";

        $results = DatabaseHelper::get_results(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $wpdb->prepare($sql,absint($form_id))
        );

        if (!is_array($results)) {
            return [];
        }

        return $results;
    }

    /**
     * Get all entries.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public function all(array $args = []): array
    {
        global $wpdb;

        $orderby = $args['orderby'] ?? 'id';
        $order = strtoupper($args['order'] ?? 'DESC');

        $allowed = [
            'id',
            'form_id',
            'status',
            'submitted_at',
        ];

        if (!in_array($orderby, $allowed, true)) {
            $orderby = 'id';
        }

        $order = ('ASC' === $order) ? 'ASC' : 'DESC';

        $query = sprintf(
            "SELECT id, form_id, user_id, status, ip, browser, referer, submitted_at
        FROM %s
        ORDER BY %s %s",
            $wpdb->prefix . 'ndfb_entries',
            $orderby,
            $order
        );

        $results = DatabaseHelper::get_results($query);

        return is_array($results) ? $results : [];
    }
}