<?php
/**
 * Forms list table.
 *
 * @package FormNova
 */

namespace FormNova\Controllers\Admin;

defined('ABSPATH') || exit;

use WP_List_Table;
use FormNova\Services\FormService;

/**
 * Forms admin table.
 */
final class FormsListTable extends WP_List_Table
{

    /**
     * Form service.
     *
     * @var FormService
     */
    private FormService $service;

    /**
     * Constructor.
     *
     * @param FormService $service Form service.
     */
    public function __construct(FormService $service)
    {

        $this->service = $service;

        parent::__construct(
            [
                'singular' => 'form',
                'plural' => 'forms',
                'ajax' => false,
            ]
        );
    }

    /**
     * Default columns renderer.
     *
     * @param array  $item Item.
     * @param string $column_name Column name.
     *
     * @return string
     */
    public function column_default($item, $column_name): string
    {

        return match ($column_name) {
            'title' => esc_html($item->title),
            'shortcode' => sprintf(
                '<input type="text"
                    class="regular-text code"
                    readonly
                    value="[formnova_form id=&quot;%d&quot;]" 
                    onclick="this.select();" />',
                (int) $item->id
            ),
            'created_at' => esc_html($item->created_at),
            default => '',
        };
    }

    /**
     * Checkbox column.
     */
    public function column_cb($item): string
    {

        return sprintf(
            '<input type="checkbox" name="form_ids[]" value="%d" />',
            (int) $item->id
        );
    }

    /**
     * Title column with row actions.
     *
     * @param object $item Item.
     *
     * @return string
     */
    public function column_title($item): string
    {

        $edit_link = wp_nonce_url(
            add_query_arg(
                [
                    'page' => 'formnova-builder',
                    'id' => absint($item->id),
                ],
                admin_url('admin.php')
            ),
            'ndfb_edit_form_' . absint($item->id)
        );

        $delete_link = wp_nonce_url(
            add_query_arg(
                [
                    'page' => 'formnova',
                    'action' => 'delete',
                    'id' => absint($item->id),
                ],
                admin_url('admin.php')
            ),
            'ndfb_delete_form'
        );

        $actions = [
            'edit' => '<a href="' . esc_url($edit_link) . '">Edit</a>',
            'delete' => '<a href="' . esc_url($delete_link) . '">Delete</a>',
        ];

        return sprintf(
            '<strong><a href="%s">%s</a></strong> %s',
            esc_url($edit_link),
            esc_html($item->title),
            $this->row_actions($actions)
        );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    public function get_columns(): array
    {

        return [
            'cb' => '<input type="checkbox" />',
            'title' => 'Title',
            'shortcode' => 'Shortcode',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Prepare items.
     */
    public function prepare_items(): void
    {
        $this->process_bulk_action();

        $per_page = 10;
        $page = $this->get_pagenum();
        $search = '';

        if (
            isset($_GET['s']) &&
            isset($_GET['formnova_search_nonce']) &&
            wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_GET['formnova_search_nonce'])
                ),
                'formnova_search'
            )
        ) {
            $search = sanitize_text_field(
                wp_unslash($_GET['s'])
            );
        }

        $data = $this->service->get_paginated($page, $per_page, $search);

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

    /**
     * Bulk actions.
     */
    public function get_bulk_actions(): array
    {
        return [
            'delete' => __('Delete', 'formnova-form-builder'),
        ];
    }

    /**
     * Process bulk actions.
     */
    public function process_bulk_action(): void
    {
        if ('delete' !== $this->current_action()) {
            return;
        }

        check_admin_referer(
            'bulk-' . $this->_args['plural']
        );

        $form_ids = isset($_POST['form_ids'])
            ? array_map(
                'absint',
                (array) wp_unslash($_POST['form_ids'])
            )
            : [];

        if (empty($form_ids)) {
            return;
        }

        foreach ($form_ids as $id) {
            $this->service->delete($id);
        }

        add_settings_error(
            'formnova',
            'form_deleted',
            __('Selected forms deleted successfully.', 'formnova-form-builder'),
            'updated'
        );

        return;
    }
}