<?php

namespace FormNova\Controllers\Admin;

defined('ABSPATH') || exit;

use FormNova\Core\View;
use FormNova\Services\EntryService;
use FormNova\Services\FormService;
use FormNova\Repository\FormRepository;
use FormNova\Repository\EntryRepository;

final class EntriesController
{

    private EntryService $service;

    private FormService $formService;

    public function __construct(
        EntryService $service,
        FormService $formService
    ) {
        $this->service = $service;
        $this->formService = $formService;
    }

    public function index(): void
    {
        global $wpdb;

        if (
            isset($_GET['action'], $_GET['id'], $_GET['_wpnonce'])
            && 'view' === sanitize_key(wp_unslash($_GET['action']))
        ) {

            if (!current_user_can('manage_options')) {
                wp_die(
                    esc_html__('You do not have permission to view this entry.', 'formnova-form-builder')
                );
            }

            $id = absint(wp_unslash($_GET['id']));
            check_admin_referer(
                'formnova_view_entry_' . $id
            );

            $this->view($id);

            return;
        }

        $table = new EntriesListTable(
            $this->service,
            new FormRepository($wpdb)
        );
        $table->prepare_items();

        View::render(
            'admin/entries/index',
            [
                'table' => $table,
                'forms' => $this->formService->all(),
            ]
        );
    }

    /**
     * Export entries as dynamic CSV.
     */
    public function export_csv(): void
    {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            wp_die(
                esc_html__(
                    'You do not have permission to export entries.',
                    'formnova-form-builder'
                )
            );
        }

        $form_id = isset($_GET['form_id'])
            ? absint(wp_unslash($_GET['form_id']))
            : 0;

        check_admin_referer(
            'formnova_export_csv_' . $form_id
        );

        if ($form_id <= 0) {
            wp_die(
                esc_html__(
                    'Invalid form selected.',
                    'formnova-form-builder'
                )
            );
        }

        $rows = (new EntryRepository($wpdb))
            ->export_csv($form_id);

        /*
|--------------------------------------------------------------------------
| Load Field Labels From Builder
|--------------------------------------------------------------------------
*/

        $field_labels = [];

        $form = $this->formService->find(
            $form_id
        );

        if (
            $form &&
            !empty($form->builder['builder'])
        ) {

            foreach ($form->builder['builder'] as $field) {

                $name = $field['name']
                    ?? $field['settings']['name']
                    ?? '';

                if (empty($name)) {
                    continue;
                }

                $label = $field['label']
                    ?? $field['settings']['label']
                    ?? $name;

                $field_labels[$name] = $label;
            }
        }

        if (empty($rows)) {

            wp_die(
                esc_html__(
                    'No entries found.',
                    'formnova-form-builder'
                )
            );
        }

        if (empty($rows)) {
            wp_die(
                esc_html__(
                    'No entries found.',
                    'formnova-form-builder'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Build Dynamic Headers & Pivot Data
        |--------------------------------------------------------------------------
        */

        $headers = [];
        $entries = [];

        foreach ($rows as $row) {

            $field_key = (string) $row->field_key;

            $field_label = $field_labels[$field_key] ?? $field_key;

            if (!isset($headers[$field_key])) {
                $headers[$field_key] = $field_label;
            }

            if (!isset($entries[$row->id])) {

                $entries[$row->id] = [
                    'Entry ID' => $row->id,
                    'Form' => $row->form_name,
                    'Submitted At' => $row->submitted_at,
                ];

            }

            $entries[$row->id][$field_label] = $row->field_value;
        }

        /*
        |--------------------------------------------------------------------------
        | Output CSV
        |--------------------------------------------------------------------------
        */

        while (ob_get_level()) {
            ob_end_clean();
        }

        nocache_headers();

        $form_title = 'form';

        $form = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT title 
        FROM {$wpdb->prefix}ndfb_forms 
        WHERE id = %d",
                $form_id
            )
        );

        if (!empty($form)) {
            $form_title = sanitize_title($form);
        }

        $filename =
            $form_title .
            '-entries-' .
            gmdate('Y-m-d-H-i-s') .
            '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header(
            'Content-Disposition: attachment; filename="' .
            $filename .
            '"'
        );

        $output = fopen('php://output', 'w');

        // UTF-8 BOM
        fprintf(
            $output,
            chr(0xEF) .
            chr(0xBB) .
            chr(0xBF)
        );

        /*
        |--------------------------------------------------------------------------
        | CSV Header
        |--------------------------------------------------------------------------
        */

        $csv_header = array_merge(
            [
                'Entry ID',
                'Form',
            ],
            $headers,
            [
                'Submitted At',
            ]
        );



        fputcsv(
            $output,
            $csv_header
        );

        /*
        |--------------------------------------------------------------------------
        | CSV Rows
        |--------------------------------------------------------------------------
        */

        foreach ($entries as $entry) {

            $line = [];

            $line[] = $entry['Entry ID'];
            $line[] = $entry['Form'];

            foreach ($headers as $field_label) {

                $line[] = $entry[$field_label] ?? '';

            }

            $line[] = $entry['Submitted At'];

            fputcsv(
                $output,
                $line
            );
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        fclose($output);

        exit;
    }

    public function view(int $id): void
    {
        $entry = $this->service->find($id);

        $meta = $this->service->get_meta($id);

        $form = $this->formService->find(
            (int) $entry->form_id
        );

        $fields = [];

        if (!empty($form->builder['builder'])) {

            foreach ($form->builder['builder'] as $field) {

                if (empty($field['name'])) {
                    continue;
                }

                $fields[$field['name']] = $field['label'] ?? $field['name'];
            }
        }

        View::render(
            'admin/entries/view',
            [
                'entry' => $entry,
                'meta' => $meta,
                'fields' => $fields,
                'form_name' => $form->title ?? 'Unknown Form',
            ]
        );
    }
}