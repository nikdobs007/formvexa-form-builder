<?php

namespace FormNova\Controllers\Admin;

defined('ABSPATH') || exit;

use WP_List_Table;
use FormNova\Services\EntryService;
use FormNova\Repository\FormRepository;

final class EntriesListTable extends WP_List_Table
{

    private EntryService $service;

    private FormRepository $form_repository;

    public function __construct(EntryService $service, FormRepository $form_repository)
    {

        $this->service = $service;
        $this->form_repository = $form_repository;

        parent::__construct([
            'singular' => __('Entry', 'formnova-form-builder'),
            'plural' => __('Entries', 'formnova-form-builder'),
            'ajax' => false,
        ]);
    }

    public function get_columns(): array
    {
        return [

            'cb' => '<input type="checkbox" />',

            'id' => 'ID',

            'form_name' => __('Form', 'formnova-form-builder'),

            'status' => __('Status', 'formnova-form-builder'),

            'submitted_at' => __('Submitted', 'formnova-form-builder'),

        ];
    }

    public function column_form_name($item): string
    {
        $form = $this->form_repository->find(
            absint($item->form_id)
        );

        if ($form) {
            return esc_html($form->title);
        }

        return 'Form #' . absint($item->form_id);
    }

    public function get_bulk_actions()
    {
        return [

            'delete' => __('Delete', 'formnova-form-builder')

        ];
    }

    public function column_cb($item): string
    {

        return sprintf(
            '<input type="checkbox" name="entry_ids[]" value="%d" />',
            (int) $item->id
        );
    }

    public function column_default($item, $column_name): string
    {

        return esc_html($item->$column_name ?? '');
    }

    public function column_id($item): string
    {
        $view_url = wp_nonce_url(
            admin_url(
                'admin.php?page=formnova-entries&action=view&id=' . (int) $item->id
            ),
            'formnova_view_entry_' . (int) $item->id
        );

        return sprintf(
            '<a href="%1$s"><strong>%2$d</strong></a>',
            esc_url($view_url),
            (int) $item->id
        );
    }

    public function prepare_items(
        int $form_id = 0
    ): void {

        $this->process_bulk_action();

        $per_page = 20;
        $page = $this->get_pagenum();

        $form_id = 0;
        $search = '';

        $form_id = isset($_REQUEST['form_id'])
            ? absint(wp_unslash($_REQUEST['form_id']))
            : 0;

        $search = isset($_REQUEST['s'])
            ? sanitize_text_field(wp_unslash($_REQUEST['s']))
            : '';

        $data = $this->service->get_filtered(
            $page,
            $per_page,
            $form_id,
            $search
        );

        $this->items = $data['items'];

        $this->_column_headers = [
            $this->get_columns(),
            [],
            [],
        ];

        $this->set_pagination_args(
            [
                'total_items' => $data['total'],
                'per_page' => $per_page,
                'total_pages' => (int) ceil($data['total'] / $per_page),
            ]
        );
    }

    public function process_bulk_action(): void
    {
        if ($this->current_action() !== 'delete') {
            return;
        }

        if (empty($_POST['entry_ids'])) {
            return;
        }

        check_admin_referer('bulk-entries');

        $ids = array_map(
            'absint',
            (array) wp_unslash($_POST['entry_ids'])
        );

        $deleted = $this->service->bulkDelete($ids);

        // Redirect yaha nahi karna.
        $_GET['deleted'] = $deleted;
    }
}