<?php
/**
 * Form repository.
 *
 * @package FormNova
 */

namespace FormNova\Repository;

defined('ABSPATH') || exit;

use FormNova\Contracts\RepositoryInterface;
use wpdb;
use FormNova\Helpers\DatabaseHelper;

/**
 * Form repository.
 */
final class FormRepository implements RepositoryInterface
{

    /**
     * Database instance.
     *
     * @var wpdb
     */
    private wpdb $wpdb;

    /**
     * Forms table.
     *
     * @var string
     */
    private string $table;

    /**
     * Constructor.
     *
     * @param wpdb $wpdb WordPress database instance.
     */
    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'ndfb_forms';
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
     * Find form by ID.
     *
     * @param int $id Form ID.
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
                title,
                slug,
                status,
                created_by,
                created_at,
                updated_at
            FROM {$wpdb->prefix}ndfb_forms
            WHERE id = %d
            LIMIT 1",
                absint($id)
            )
        );

        return ($result instanceof \stdClass) ? $result : null;
    }

    /**
     * Find form by slug.
     *
     * @param string $slug Form slug.
     *
     * @return object|null
     */
    public function find_by_slug(string $slug): ?object
    {
        global $wpdb;

        $result = DatabaseHelper::get_row(
            $wpdb->prepare(
                "SELECT
                id,
                title,
                slug,
                status,
                created_by,
                created_at,
                updated_at
            FROM {$wpdb->prefix}ndfb_forms
            WHERE slug = %s
            LIMIT 1",
                $slug
            )
        );

        return ($result instanceof \stdClass) ? $result : null;
    }

    /**
     * Get paginated forms.
     *
     * @param int    $page     Current page.
     * @param int    $per_page Per page.
     * @param string $order_by Order by column.
     * @param string $order    ASC|DESC.
     *
     * @return array
     */
    /**
     * Paginated results (FIXED: interface compatible).
     */
    public function paginate(
        int $page = 1,
        int $per_page = 20,
        string $orderby = 'id',
        string $order = 'DESC'
    ): array {

        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $allowed_orderby = [
            'id' => 'id',
            'title' => 'title',
            'slug' => 'slug',
            'status' => 'status',
            'created_at' => 'created_at',
        ];

        $orderby = $allowed_orderby[$orderby] ?? 'id';
        $order = ('ASC' === strtoupper($order)) ? 'ASC' : 'DESC';

        return DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
            id,
            title,
            slug,
            status,
            created_by,
            created_at,
            updated_at
            FROM {$wpdb->prefix}ndfb_forms
            ORDER BY " . esc_sql($orderby) . ' ' . esc_sql($order) . "
            LIMIT %d OFFSET %d",
                absint($per_page),
                absint($offset)
            )
        );
    }

    /**
     * Search forms.
     *
     * @param string $keyword Search keyword.
     * @param int    $page    Current page.
     * @param int    $per_page Per page.
     *
     * @return array
     */
    /**
     * Search + count (safe extension layer)
     */
    public function search_count(string $search = ''): int
    {
        global $wpdb;

        if (empty($search)) {
            return (int) DatabaseHelper::get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ndfb_forms"
            );
        }

        $like = '%' . $wpdb->esc_like($search) . '%';

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
             FROM {$wpdb->prefix}ndfb_forms
             WHERE title LIKE %s
             OR slug LIKE %s",
                $like,
                $like
            )
        );
    }

    /**
     * Count forms.
     *
     * @return int
     */
    public function count(string $search = ''): int
    {
        global $wpdb;

        if (!empty($search)) {

            $like = '%' . $wpdb->esc_like($search) . '%';

            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*)
                 FROM {$wpdb->prefix}ndfb_forms
                 WHERE title LIKE %s
                 OR slug LIKE %s",
                    $like,
                    $like
                )
            );
        }

        return (int) DatabaseHelper::get_var(
            "SELECT COUNT(*)
         FROM {$wpdb->prefix}ndfb_forms"
        );
    }

    public function all(array $args = []): array
    {
        global $wpdb;

        $orderby = $args['orderby'] ?? 'id';
        $order = strtoupper($args['order'] ?? 'DESC');

        $allowed = [
            'id',
            'title',
            'slug',
            'status',
            'created_at',
        ];

        if (!in_array($orderby, $allowed, true)) {
            $orderby = 'id';
        }

        $order = ($order === 'ASC') ? 'ASC' : 'DESC';

        $results = DatabaseHelper::get_results(
            "SELECT
            id,
            title,
            slug,
            status,
            created_by,
            created_at,
            updated_at
        FROM {$wpdb->prefix}ndfb_forms
        ORDER BY {$orderby} {$order}"
        );

        return is_array($results) ? $results : [];
    }

    public function search(
        string $keyword,
        int $page = 1,
        int $per_page = 20
    ): array {

        global $wpdb;

        $offset = ($page - 1) * $per_page;

        $like = '%' . $wpdb->esc_like($keyword) . '%';

        return DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                id,
                title,
                slug,
                status,
                created_by,
                created_at,
                updated_at
            FROM {$wpdb->prefix}ndfb_forms
            WHERE title LIKE %s
               OR slug LIKE %s
            ORDER BY id DESC
            LIMIT %d OFFSET %d",
                $like,
                $like,
                $per_page,
                $offset
            )
        );
    }

    /**
     * Create form.
     *
     * @param array $data Form data.
     *
     * @return int
     */
    public function create(array $data): int
    {

        $result = $this->wpdb->insert(
            $this->table,
            [
                'title' => sanitize_text_field($data['title'] ?? ''),
                'slug' => sanitize_title($data['slug'] ?? ''),
                'status' => sanitize_key($data['status'] ?? 'draft'),
                'created_by' => absint($data['created_by'] ?? get_current_user_id()),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ],
            [
                '%s',
                '%s',
                '%s',
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
     * Update form.
     *
     * @param int   $id   Form ID.
     * @param array $data Form data.
     *
     * @return bool
     */
    public function update(int $id, array $data): bool
    {

        $update = [];

        $formats = [];

        if (isset($data['title'])) {
            $update['title'] = sanitize_text_field($data['title']);
            $formats[] = '%s';
        }

        if (isset($data['slug'])) {
            $update['slug'] = sanitize_title($data['slug']);
            $formats[] = '%s';
        }

        if (isset($data['status'])) {
            $update['status'] = sanitize_key($data['status']);
            $formats[] = '%s';
        }

        $update['updated_at'] = current_time('mysql');
        $formats[] = '%s';

        $result = $this->wpdb->update(
            $this->table,
            $update,
            [
                'id' => $id,
            ],
            $formats,
            [
                '%d',
            ]
        );

        return false !== $result;
    }

    /**
     * Update form status.
     *
     * @param int    $id     Form ID.
     * @param string $status Status.
     *
     * @return bool
     */
    public function update_status(int $id, string $status): bool
    {

        $result = $this->wpdb->update(
            $this->table,
            [
                'status' => sanitize_key($status),
                'updated_at' => current_time('mysql'),
            ],
            [
                'id' => $id,
            ],
            [
                '%s',
                '%s',
            ],
            [
                '%d',
            ]
        );

        return false !== $result;
    }

    /**
     * Delete form.
     *
     * @param int $id Form ID.
     *
     * @return bool
     */
    public function delete(int $id): bool
    {

        $result = $this->wpdb->delete(
            $this->table,
            [
                'id' => $id,
            ],
            [
                '%d',
            ]
        );

        return false !== $result;
    }

    /**
     * Bulk delete forms.
     *
     * @param array<int> $ids Form IDs.
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
                "DELETE FROM {$wpdb->prefix}ndfb_forms WHERE id IN (" .
                implode(',', array_fill(0, count($ids), '%d')) .
                ")",
                ...$ids
            )
        );

        return false === $result ? 0 : (int) $result;
    }

    /**
     * Check slug exists.
     *
     * @param string   $slug      Slug.
     * @param int|null $exclude_id Exclude ID.
     *
     * @return bool
     */
    public function slug_exists(
        string $slug,
        ?int $exclude_id = null
    ): bool {

        global $wpdb;

        if (null === $exclude_id) {

            return (int) DatabaseHelper::get_var(
                $wpdb->prepare(
                    "SELECT COUNT(id)
                FROM {$wpdb->prefix}ndfb_forms
                WHERE slug = %s",
                    sanitize_title($slug)
                )
            ) > 0;
        }

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_forms
            WHERE slug = %s
            AND id != %d",
                sanitize_title($slug),
                absint($exclude_id)
            )
        ) > 0;
    }

    /**
     * Check form exists.
     *
     * @param int $id Form ID.
     *
     * @return bool
     */
    public function exists(int $id): bool
    {
        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_forms
            WHERE id = %d",
                absint($id)
            )
        ) > 0;
    }

    /**
     * Get forms by status.
     *
     * @param string $status Form status.
     *
     * @return array
     */
    public function get_by_status(string $status): array
    {
        global $wpdb;

        $result = DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                id,
                title,
                slug,
                status,
                created_by,
                created_at,
                updated_at
            FROM {$wpdb->prefix}ndfb_forms
            WHERE status = %s
            ORDER BY id DESC",
                sanitize_key($status)
            )
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Get recent forms.
     *
     * @param int $limit Number of forms.
     *
     * @return array
     */
    public function recent(int $limit = 10): array
    {
        global $wpdb;

        $limit = max(1, absint($limit));

        $result = DatabaseHelper::get_results(
            $wpdb->prepare(
                "SELECT
                id,
                title,
                slug,
                status,
                created_by,
                created_at,
                updated_at
            FROM {$wpdb->prefix}ndfb_forms
            ORDER BY created_at DESC
            LIMIT %d",
                $limit
            )
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Get form options for dropdowns.
     *
     * @return array<int, string>
     */
    public function dropdown(): array
    {
        global $wpdb;

        $rows = DatabaseHelper::get_results(
            "SELECT
                id,
                title
            FROM {$wpdb->prefix}ndfb_forms
            ORDER BY title ASC"
        );

        $options = [];

        foreach ($rows as $row) {
            $options[(int) $row->id] = $row->title;
        }

        return $options;
    }

    /**
     * Duplicate form.
     *
     * @param int $id Form ID.
     *
     * @return int
     */
    public function duplicate(int $id): int
    {

        $form = $this->find($id);

        if (null === $form) {
            return 0;
        }

        return $this->create(
            [
                'title' => $form->title . ' Copy',
                'slug' => wp_unique_post_slug(
                    $form->slug . '-copy',
                    0,
                    'draft',
                    'post',
                    0
                ),
                'status' => 'draft',
                'created_by' => get_current_user_id(),
            ]
        );
    }

    /**
     * Get total forms by status.
     *
     * @param string $status Form status.
     *
     * @return int
     */
    public function count_by_status(string $status): int
    {
        global $wpdb;

        return (int) DatabaseHelper::get_var(
            $wpdb->prepare(
                "SELECT COUNT(id)
            FROM {$wpdb->prefix}ndfb_forms
            WHERE status = %s",
                sanitize_key($status)
            )
        );
    }

    /**
     * Truncate forms table.
     *
     * @return bool
     */
    public function truncate(): bool
    {
        global $wpdb;

        $result = DatabaseHelper::query(
            "TRUNCATE TABLE {$wpdb->prefix}ndfb_forms"
        );

        return false !== $result;
    }

    /**
     * Get last inserted form.
     *
     * @return object|null
     */
    public function latest(): ?object
    {
        global $wpdb;

        $result = DatabaseHelper::get_row(
            "
        SELECT
            id,
            title,
            slug,
            status,
            created_by,
            created_at,
            updated_at
        FROM {$wpdb->prefix}ndfb_forms
        ORDER BY id DESC
        LIMIT 1
        "
        );

        return ($result instanceof \stdClass) ? $result : null;
    }

    /**
     * Get total pages.
     *
     * @param int $per_page Items per page.
     *
     * @return int
     */
    public function total_pages(int $per_page = 20): int
    {

        $per_page = max(1, absint($per_page));

        return (int) ceil($this->count() / $per_page);
    }

    /**
     * Get repository name.
     *
     * @return string
     */
    public function name(): string
    {

        return 'forms';
    }
}