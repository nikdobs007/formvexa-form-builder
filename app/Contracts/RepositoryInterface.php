<?php
/**
 * Repository Interface.
 *
 * @package formvexa
 */

namespace formvexa\Contracts;

defined('ABSPATH') || exit;

/**
 * Base repository contract.
 */
interface RepositoryInterface
{

    /**
     * Get record by ID.
     *
     * @param int $id Record ID.
     *
     * @return object|null
     */
    public function find(int $id): ?object;

    /**
     * Get all records.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public function all(array $args = []): array;

    /**
     * Create record.
     *
     * @param array $data Record data.
     *
     * @return int
     */
    public function create(array $data): int;

    /**
     * Update record.
     *
     * @param int   $id   Record ID.
     * @param array $data Record data.
     *
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete record.
     *
     * @param int $id Record ID.
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Bulk delete records.
     *
     * @param array $ids Record IDs.
     *
     * @return int Number of deleted rows.
     */
    public function bulk_delete(array $ids): int;

    /**
     * Get paginated records.
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
    ): array;

    /**
     * Search records.
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
    ): array;

    public function count(): int;

    /**
     * Check record exists.
     *
     * @param int $id Record ID.
     *
     * @return bool
     */
    public function exists(int $id): bool;
}