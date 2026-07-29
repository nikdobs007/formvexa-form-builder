<?php

namespace FormNova\Core;

defined('ABSPATH') || exit;

use FormNova\Controllers\Admin\FormsController;
use FormNova\Controllers\Admin\EntriesController;
use FormNova\Controllers\Admin\SettingsController;
use FormNova\Controllers\Admin\BuilderController;
use FormNova\Services\EntryService;
use FormNova\Services\MetaService;
use FormNova\Repository\EntryMetaRepository;
use FormNova\Repository\EntryRepository;
use FormNova\Repository\FormRepository;
use FormNova\Repository\MetaRepository;
use FormNova\Services\FormService;



/**
 * Admin menu.
 */
final class AdminMenu
{

    /**
     * Register hooks.
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_init', [$this, 'handle_form_actions']);
        add_action(
            'admin_post_formnova_export_csv',
            [$this, 'export_entries_csv']
        );
    }

    /**
     * Register menu pages.
     */
    public function register_menu(): void
    {
        add_menu_page(
            'FormNova',
            'FormNova',
            'manage_options',
            'formnova',
            [$this, 'forms_page'],
            'dashicons-feedback',
            26
        );

        add_submenu_page(
            'formnova',
            'All Forms',
            'All Forms',
            'manage_options',
            'formnova',
            [$this, 'forms_page']
        );

        add_submenu_page(
            'formnova',
            'Add New',
            'Add New',
            'manage_options',
            'formnova-builder',
            [$this, 'builder_page']
        );

        add_submenu_page(
            'formnova',
            'Entries',
            'Entries',
            'manage_options',
            'formnova-entries',
            [$this, 'entries_page']
        );

        add_submenu_page(
            'formnova',
            'Settings',
            'Settings',
            'manage_options',
            'formnova-settings',
            [$this, 'settings_page']
        );
    }

    /**
     * Forms page.
     */
    public function forms_page(): void
    {
        global $wpdb;

        $formRepository = new FormRepository($wpdb);
        $metaRepository = new MetaRepository($wpdb);

        $service = new FormService(
            $formRepository,
            $metaRepository
        );

        $controller = new FormsController($service);
        $controller->index();
    }

    public function builder_page(): void
    {
        global $wpdb;

        $id = isset($_GET['id'])
            ? absint(wp_unslash($_GET['id']))
            : 0;

        // if ($id > 0) {
        //     check_admin_referer('ndfb_edit_form_' . $id);
        // }

        $formRepository = new FormRepository($wpdb);
        $metaRepository = new MetaRepository($wpdb);

        $service = new FormService(
            $formRepository,
            $metaRepository
        );

        $controller = new BuilderController($service);

        $controller->index($id);
    }

    /**
     * Entries page.
     */
    public function entries_page(): void
    {
        global $wpdb;

        $entryService = new EntryService(
            new EntryRepository($wpdb),
            new EntryMetaRepository($wpdb)
        );

        $formService = new FormService(
            new FormRepository($wpdb),
            new MetaRepository($wpdb)
        );

        $controller = new EntriesController(
            $entryService,
            $formService
        );

        $controller->index();
    }

    /**
     * Settings page.
     */
    public function settings_page(): void
    {
        $controller = new SettingsController();
        $controller->index();
    }

    public function handle_form_actions(): void
    {
        if (!is_admin()) {
            return;
        }

        $page = isset($_GET['page'])
            ? sanitize_key(wp_unslash($_GET['page']))
            : '';

        if ('formnova' !== $page) {
            return;
        }

        $action = isset($_GET['action'])
            ? sanitize_key(wp_unslash($_GET['action']))
            : '';

        if ('delete' !== $action) {
            return;
        }

        check_admin_referer('ndfb_delete_form');

        $id = isset($_GET['id'])
            ? absint(wp_unslash($_GET['id']))
            : 0;

        if (!$id) {
            return;
        }

        global $wpdb;

        $formRepository = new FormRepository($wpdb);
        $metaRepository = new MetaRepository($wpdb);

        $service = new FormService(
            $formRepository,
            $metaRepository
        );

        $service->delete($id);

        wp_safe_redirect(admin_url('admin.php?page=formnova'));
        exit;
    }

    /**
     * Export Entries CSV.
     */
    public function export_entries_csv(): void
    {
        global $wpdb;

        $entryService = new EntryService(
            new EntryRepository($wpdb),
            new EntryMetaRepository($wpdb)
        );

        $formService = new FormService(
            new FormRepository($wpdb),
            new MetaRepository($wpdb)
        );

        $controller = new EntriesController(
            $entryService,
            $formService
        );

        $controller->export_csv();
    }
}