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
            $id = absint(wp_unslash($_GET['id']));
            $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));

            if (
                !wp_verify_nonce(
                    $nonce,
                    'formnova_view_entry_' . $id
                )
            ) {
                wp_die(
                    esc_html__('Security check failed.', 'formnova-form')
                );
            }

            $this->view($id);

            return;
        }

        if (
            isset($_GET['action'], $_GET['id'])
            && $_GET['action'] === 'view'
        ) {

            $this->view(
                absint($_GET['id'])
            );

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

        $form_id = isset($_GET['form_id'])
            ? absint(wp_unslash($_GET['form_id']))
            : 0;

        if ($form_id <= 0) {
            wp_die(
                esc_html__(
                    'Invalid form selected.',
                    'formnova-form'
                )
            );
        }

        $rows = (new EntryRepository($wpdb))
            ->export_csv($form_id);

        if (empty($rows)) {
            wp_die(
                esc_html__(
                    'No entries found.',
                    'formnova-form'
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

            if (!in_array($field_key, $headers, true)) {
                $headers[] = $field_key;
            }

            if (!isset($entries[$row->id])) {

                $entries[$row->id] = [
                    'Entry ID' => $row->id,
                    'Form' => $row->form_name,
                    'Submitted At' => $row->submitted_at,
                ];

            }

            $entries[$row->id][$field_key] = $row->field_value;
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

        $filename =
            'form-' .
            $form_id .
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

            foreach ($headers as $field_key) {

                $line[] = $entry[$field_key] ?? '';

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

        View::render('admin/entries/view', [
            'entry' => $entry,
            'meta' => $meta,
            'form_name' => $form->title ?? 'Unknown Form',
        ]);
    }
}