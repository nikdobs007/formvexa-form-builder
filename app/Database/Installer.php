<?php
/**
 * Database installer.
 *
 * @package FormNova
 */

namespace FormNova\Database;

defined('ABSPATH') || exit;

/**
 * Handles plugin database installation.
 */
final class Installer
{

    /**
     * Install or upgrade database.
     *
     * @return void
     */
    public static function install(): void
    {

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach (Schema::get_tables() as $sql) {
            dbDelta($sql);
        }

        update_option('ndfb_db_version', NDFB_DB_VERSION);

        Migration::run();
    }

    /**
     * Check whether database upgrade is required.
     *
     * @return void
     */
    public static function maybe_upgrade(): void
    {

        $current_version = get_option('ndfb_db_version', '');

        if (version_compare($current_version, NDFB_DB_VERSION, '<')) {
            self::install();
        }
    }
}