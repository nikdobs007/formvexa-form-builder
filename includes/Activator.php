<?php
/**
 * Plugin activator.
 *
 * @package FormNova
 */

namespace FormNova;

defined('ABSPATH') || exit;

use FormNova\Database\Installer;

final class Activator
{

    /**
     * Activate plugin.
     *
     * @return void
     */
    public static function activate(): void
    {

        Installer::install();

        if (!get_option('ndfb_activation_time')) {
            add_option('ndfb_activation_time', time());
        }

        update_option('ndfb_version', NDFB_VERSION);
        update_option('ndfb_db_version', NDFB_DB_VERSION);
    }
}