<?php
/**
 * Database schema.
 *
 * @package formvexa
 */

namespace formvexa\Database;

defined('ABSPATH') || exit;

/**
 * Database schema builder.
 */
final class Schema
{

    /**
     * Get all database table schemas.
     *
     * @global \wpdb $wpdb WordPress database abstraction object.
     *
     * @return array<string>
     */
    public static function get_tables(): array
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $wpdb->prefix;

        return [
            self::forms_table($prefix, $charset_collate),
            self::form_meta_table($prefix, $charset_collate),
            self::entries_table($prefix, $charset_collate),
            self::entry_meta_table($prefix, $charset_collate),
        ];
    }

    /**
     * Forms table.
     *
     * @param string $prefix            Table prefix.
     * @param string $charset_collate   Charset and collation.
     *
     * @return string
     */
    private static function forms_table(
        string $prefix,
        string $charset_collate
    ): string {

        return "CREATE TABLE {$prefix}ndfb_forms (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(191) NOT NULL,
			slug VARCHAR(191) NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'draft',
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY slug (slug),
			KEY status (status),
			KEY created_by (created_by)
		) {$charset_collate};";
    }

    /**
     * Form meta table.
     *
     * @param string $prefix            Table prefix.
     * @param string $charset_collate   Charset and collation.
     *
     * @return string
     */
    private static function form_meta_table(
        string $prefix,
        string $charset_collate
    ): string {

        return "CREATE TABLE {$prefix}ndfb_form_meta (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id BIGINT(20) UNSIGNED NOT NULL,
			meta_key VARCHAR(191) NOT NULL,
			meta_value LONGTEXT NULL,
			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY meta_key (meta_key),
            KEY form_id_meta_key (form_id, meta_key)
		) {$charset_collate};";
    }

    /**
     * Entries table.
     *
     * @param string $prefix            Table prefix.
     * @param string $charset_collate   Charset and collation.
     *
     * @return string
     */
    private static function entries_table(
        string $prefix,
        string $charset_collate
    ): string {

        return "CREATE TABLE {$prefix}ndfb_entries (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			form_id BIGINT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'completed',
			ip VARCHAR(45) DEFAULT NULL,
			browser TEXT NULL,
			referer TEXT NULL,
			submitted_at DATETIME NOT NULL,
			PRIMARY KEY (id),
			KEY form_id (form_id),
			KEY user_id (user_id),
			KEY status (status)
		) {$charset_collate};";
    }

    /**
     * Entry meta table.
     *
     * @param string $prefix            Table prefix.
     * @param string $charset_collate   Charset and collation.
     *
     * @return string
     */
    private static function entry_meta_table(
        string $prefix,
        string $charset_collate
    ): string {

        return "CREATE TABLE {$prefix}ndfb_entry_meta (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			entry_id BIGINT(20) UNSIGNED NOT NULL,
			field_key VARCHAR(191) NOT NULL,
			field_value LONGTEXT NULL,
			PRIMARY KEY (id),
			KEY entry_id (entry_id),
			KEY field_key (field_key)
		) {$charset_collate};";
    }
}