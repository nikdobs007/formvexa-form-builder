<?php
/**
 * Plugin Name: Formvexa – Drag & Drop Contact Form Builder
 * Plugin URI: https://github.com/nikdobs007/formvexa-form-builder
 * Description: Build powerful WordPress contact forms with drag-and-drop builder, AJAX submissions, file uploads, custom fields, analytics, email notifications, and submission management.
 * Version: 1.0.0
 * Author: Nikunj Dobariya
 * Author URI: https://profiles.wordpress.org/nikdobs/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: formvexa-form-builder
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.1
 */

defined('ABSPATH') || exit;

define( 'NDFB_VERSION', '1.0.0' );
define( 'NDFB_DB_VERSION', '1.0.0' );
define('NDFB_PLUGIN_FILE', __FILE__);
define('NDFB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NDFB_PLUGIN_URL', plugin_dir_url(__FILE__));

if (file_exists(NDFB_PLUGIN_PATH . 'vendor/autoload.php')) {
    require_once NDFB_PLUGIN_PATH . 'vendor/autoload.php';
}

register_activation_hook(
    NDFB_PLUGIN_FILE,
    [formvexa\Activator::class, 'activate']
);

register_deactivation_hook(
    NDFB_PLUGIN_FILE,
    [formvexa\Deactivator::class, 'deactivate']
);

add_action('plugins_loaded', [formvexa\Core\Plugin::class, 'boot']);