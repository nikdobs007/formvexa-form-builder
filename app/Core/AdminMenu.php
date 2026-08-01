<?php

namespace formvexa\Core;

defined('ABSPATH') || exit;

use formvexa\Controllers\Admin\FormsController;
use formvexa\Controllers\Admin\EntriesController;
use formvexa\Controllers\Admin\SettingsController;
use formvexa\Controllers\Admin\BuilderController;
use formvexa\Services\EntryService;
use formvexa\Services\MetaService;
use formvexa\Repository\EntryMetaRepository;
use formvexa\Repository\EntryRepository;
use formvexa\Repository\FormRepository;
use formvexa\Repository\MetaRepository;
use formvexa\Services\FormService;



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
            'admin_post_formvexa_export_csv',
            [$this, 'export_entries_csv']
        );
    }

    /**
     * Register menu pages.
     */
    public function register_menu(): void
    {
        add_menu_page(
            'formvexa',
            'formvexa',
            'manage_options',
            'formvexa',
            [$this, 'forms_page'],
            'dashicons-feedback',
            26
        );

        add_submenu_page(
            'formvexa',
            'All Forms',
            'All Forms',
            'manage_options',
            'formvexa',
            [$this, 'forms_page']
        );

        add_submenu_page(
            'formvexa',
            'Add New',
            'Add New',
            'manage_options',
            'formvexa-builder',
            [$this, 'builder_page']
        );

        add_submenu_page(
            'formvexa',
            'Entries',
            'Entries',
            'manage_options',
            'formvexa-entries',
            [$this, 'entries_page']
        );

        add_submenu_page(
            'formvexa',
            'Settings',
            'Settings',
            'manage_options',
            'formvexa-settings',
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

        if ('formvexa' !== $page) {
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

        wp_safe_redirect(admin_url('admin.php?page=formvexa'));
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