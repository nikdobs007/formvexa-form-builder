<?php

namespace formvexa\Controllers\Admin;

defined('ABSPATH') || exit;

use formvexa\Core\View;
use formvexa\Services\EntryService;
use formvexa\Services\FormService;
use formvexa\Repository\FormRepository;
use formvexa\Repository\EntryRepository;

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
                    esc_html__('You do not have permission to view this entry.', 'formvexa-form-builder')
                );
            }

            $id = absint(wp_unslash($_GET['id']));
            check_admin_referer(
                'formvexa_view_entry_' . $id
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
                    'formvexa-form-builder'
                )
            );
        }

        $form_id = isset($_GET['form_id'])
            ? absint(wp_unslash($_GET['form_id']))
            : 0;

        check_admin_referer(
            'formvexa_export_csv_' . $form_id
        );

        if ($form_id <= 0) {
            wp_die(
                esc_html__(
                    'Invalid form selected.',
                    'formvexa-form-builder'
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
                    'formvexa-form-builder'
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

        $form_name = $form->title ?? '';

        foreach ($rows as $row) {

            $entry = [
                'Entry ID' => $row->id,
                'Form' => $form_name,
                'Submitted At' => $row->submitted_at,
            ];

            if (!empty($row->entry_data) && is_array($row->entry_data)) {

                foreach ($row->entry_data as $key => $value) {

                    $label = $field_labels[$key] ?? $key;

                    if (!isset($headers[$label])) {
                        $headers[$label] = $label;
                    }

                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $entry[$label] = $value;
                }
            }

            $entries[] = $entry;
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

            foreach ($headers as $label) {
                $line[] = $entry[$label] ?? '';
            }

            $line[] = $entry['Submitted At'];

            fputcsv($output, $line);
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
        fclose($output);

        exit;
    }

    public function view(int $id): void
    {
        $entry = $this->service->find($id);

        if (!$entry) {
            wp_die(
                esc_html__('Entry not found.', 'formvexa-form-builder')
            );
        }

        $entryData = is_array($entry->entry_data)
            ? $entry->entry_data
            : [];

        $form = $this->formService->find(
            (int) $entry->form_id
        );

        $fields = [];

        if (
            $form &&
            !empty($form->builder['builder'])
        ) {

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
                'entry_data' => $entryData,
                'fields' => $fields,
                'form_name' => $form->title ?? 'Unknown Form',
            ]
        );
    }
}