<?php
/**
 * Database migration handler.
 *
 * @package formvexa
 */

namespace formvexa\Database;

defined('ABSPATH') || exit;

/**
 * Handles database migrations.
 */
final class Migration
{

    /**
     * Run database migrations.
     *
     * @return void
     */
    public static function run(): void
    {

        $current_version = get_option('ndfb_db_version', '');

        if (empty($current_version)) {
            update_option('ndfb_db_version', NDFB_DB_VERSION);

            return;
        }

        if (version_compare($current_version, NDFB_DB_VERSION, '>=')) {
            return;
        }

        self::migrate($current_version);

        update_option('ndfb_db_version', NDFB_DB_VERSION);
    }

    /**
     * Execute version-specific migrations.
     *
     * @param string $current_version Current database version.
     *
     * @return void
     */
    private static function migrate(string $current_version): void
    {

        /**
         * Future migrations.
         *
         * Example:
         *
         * if ( version_compare( $current_version, '1.1.0', '<' ) ) {
         *     self::migrate_to_110();
         * }
         */

        do_action('ndfb_database_migration', $current_version, NDFB_DB_VERSION);
    }
}