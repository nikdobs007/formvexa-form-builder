<?php
/**
 * Entry Service.
 *
 * @package formvexa
 */

namespace formvexa\Services;

defined('ABSPATH') || exit;

use InvalidArgumentException;
use WP_Error;
use formvexa\Repository\EntryRepository;
use formvexa\Repository\EntryMetaRepository;
use formvexa\Services\FileUploadService;
use formvexa\Fields\Registry;

/**
 * Handles all business logic for Entries.
 */
final class EntryService
{
    /**
     * Entry repository.
     *
     * @var EntryRepository
     */
    private EntryRepository $repository;

    /**
     * Entry meta repository.
     *
     * @var EntryMetaRepository
     */
    private EntryMetaRepository $meta;

    /**
     * Constructor.
     *
     * @param EntryRepository     $repository Entry repository.
     * @param EntryMetaRepository $meta       Entry meta repository.
     */
    public function __construct(
        EntryRepository $repository,
        EntryMetaRepository $meta
    ) {
        $this->repository = $repository;
        $this->meta = $meta;
    }

    /* -----------------------------------------------------------------
    | Read Operations
    |-----------------------------------------------------------------*/

    /**
     * Find entry.
     *
     * @param int $id Entry ID.
     *
     * @return object|null
     */
    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    /**
     * Entry exists.
     *
     * @param int $id Entry ID.
     *
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }

    /**
     * Paginated entries.
     *
     * @param int $page
     * @param int $per_page
     *
     * @return array
     */
    public function get_paginated(
        int $page,
        int $per_page,
        int $form_id = 0,
        string $search = ''
    ): array {

        return [

            'items' => $this->repository->paginate(
                $page,
                $per_page,
                $form_id,
                $search
            ),

            'total' => $this->repository->count(
                $form_id,
                $search
            ),

        ];

    }

    /**
     * Count entries.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->repository->count();
    }

    /**
     * Get entry meta.
     *
     * @param int $entry_id Entry ID.
     *
     * @return array
     */
    public function get_meta(
        int $entry_id
    ): array {

        return $this->meta->all(
            $entry_id
        );
    }

    /* -----------------------------------------------------------------
    | CRUD
    |-----------------------------------------------------------------*/

    /**
     * Create entry.
     *
     * @param array $data Entry data.
     *
     * @return int
     */
    public function create(
        array $data
    ): int {

        if (empty($data['form_id'])) {
            return 0;
        }

        return $this->repository->create(
            wp_parse_args(
                $data,
                [
                    'user_id' => get_current_user_id(),
                    'status' => 'new',
                    'ip' => sanitize_text_field(
                        wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')
                    ),
                    'browser' => sanitize_text_field(
                        wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')
                    ),
                    'referer' => esc_url_raw(
                        wp_unslash($_SERVER['HTTP_REFERER'] ?? '')
                    ),
                ]
            )
        );
    }

    /**
     * Update entry.
     *
     * @param int   $id
     * @param array $data
     *
     * @return bool
     */
    public function update(
        int $id,
        array $data
    ): bool {

        if (!$this->exists($id)) {
            return false;
        }

        return $this->repository->update(
            $id,
            $data
        );
    }

    /**
     * Delete entry.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(
        int $id
    ): bool {

        if (!$this->exists($id)) {
            return false;
        }

        $this->meta->delete_by_entry(
            $id
        );

        return $this->repository->delete(
            $id
        );
    }

    /**
     * Bulk delete.
     *
     * @param array $ids
     *
     * @return int
     */
    public function bulkDelete(
        array $ids
    ): int {

        $deleted = 0;

        foreach ($ids as $id) {

            if ($this->delete(absint($id))) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Validate submission.
     *
     * @param int   $form_id Form ID.
     * @param array $fields  Submitted fields.
     * @param array $request Request data.
     * @param array $files   Uploaded files.
     *
     * @return true|WP_Error
     */
    private function validate(
        int $form_id,
        array $fields,
        array $request,
        array $files = []
    ) {

        if ($form_id <= 0) {
            return new WP_Error(
                'invalid_form',
                __('Invalid form.', 'formvexa-form-builder')
            );
        }

        if (empty($fields)) {
            return new WP_Error(
                'empty_fields',
                __('No form fields found.', 'formvexa-form-builder')
            );
        }

        if (!is_array($request)) {
            return new WP_Error(
                'invalid_request',
                __('Invalid request.', 'formvexa-form-builder')
            );
        }

        /*
|--------------------------------------------------------------------------
| Prevent Completely Empty Submission
|--------------------------------------------------------------------------
*/

        $hasValue = false;

        foreach ($request as $value) {

            if (is_array($value)) {

                if (!empty(array_filter($value))) {
                    $hasValue = true;
                    break;
                }

            } elseif (trim((string) $value) !== '') {

                $hasValue = true;
                break;
            }
        }

        if (!$hasValue) {

            foreach ($files as $file) {

                if (
                    isset($file['error']) &&
                    $file['error'] === UPLOAD_ERR_OK
                ) {
                    $hasValue = true;
                    break;
                }
            }
        }

        if (!$hasValue) {

            return new WP_Error(
                'empty_submission',
                __('Please fill at least one field before submitting.', 'formvexa-form-builder')
            );
        }

        foreach ($fields as $field) {

            if (!is_array($field)) {
                continue;
            }

            $key = sanitize_key(
                $field['name'] ?? ''
            );

            if ($key === '') {
                continue;
            }

            $required = !empty($field['required']);

            if (!$required) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Required File Validation
            |--------------------------------------------------------------------------
            */

            $type = sanitize_key(
                $field['type'] ?? 'text'
            );

            if ($type === 'file') {

                if (
                    empty($field['required']) &&
                    (
                        !isset($files[$key]) ||
                        $files[$key]['error'] === UPLOAD_ERR_NO_FILE
                    )
                ) {
                    continue;
                }

                if (
                    !isset($files[$key]) ||
                    !isset($files[$key]['error']) ||
                    $files[$key]['error'] === UPLOAD_ERR_NO_FILE
                ) {

                    return new WP_Error(
                        'required_file',
                        sprintf(
                            /* translators: %s: Field label. */
                            __('%s is required.', 'formvexa-form-builder'),
                            $field['label'] ?? $key
                        )
                    );

                }

                if ($files[$key]['error'] !== UPLOAD_ERR_OK) {

                    return new WP_Error(
                        'upload_error',
                        sprintf(
                            /* translators: %s: Field label. */
                            __('Unable to upload %s.', 'formvexa-form-builder'),
                            $field['label'] ?? $key
                        )
                    );

                }

                $uploader = new FileUploadService();

                $result = $uploader->validate(
                    $files[$key] ?? [],
                    $field
                );

                if (is_wp_error($result)) {
                    return $result;
                }

                continue;
            }

            $value = $request[$key]
                ?? $request[$key . '[]']
                ?? '';

            $fieldObject = Registry::make($field);

            if ($fieldObject) {

                $result = $fieldObject->validate($value);

                if ($result !== true) {

                    return new WP_Error(
                        'validation_error',
                        is_string($result)
                        ? $result
                        : sprintf(
                            /* translators: %s: Field label. */
                            __('%s is invalid.', 'formvexa-form-builder'),
                            $field['label'] ?? $key
                        )
                    );

                }

            }

            if (is_array($value)) {

                $value = array_filter($value);

                if (empty($value)) {

                    return new WP_Error(
                        'required_field',
                        sprintf(
                            /* translators: %s: Field label. */
                            __('%s is required.', 'formvexa-form-builder'),
                            $field['label'] ?? $key
                        )
                    );
                }

                continue;
            }

            if (trim((string) $value) === '') {

                return new WP_Error(
                    'required_field',
                    sprintf(
                        /* translators: %s: Field label. */
                        __('%s is required.', 'formvexa-form-builder'),
                        $field['label'] ?? $key
                    )
                );
            }
        }

        return true;
    }

    /**
     * Sanitize submitted fields.
     *
     * @param array $fields
     * @param array $request
     * @param array $files
     *
     * @return array
     */
    private function sanitize_fields(
        array $fields,
        array $request,
        array $files = []
    ) {

        $clean = [];

        foreach ($fields as $field) {

            if (!is_array($field)) {
                continue;
            }

            $key = sanitize_key(
                $field['name'] ?? ''
            );

            if ($key === '') {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Create Field Object
            |--------------------------------------------------------------------------
            */

            $fieldObject = \formvexa\Fields\Registry::make(
                $field
            );

            if (!$fieldObject) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get Submitted Value
            |--------------------------------------------------------------------------
            */

            $value = $request[$key]
                ?? $request[$key . '[]']
                ?? '';

            /*
            |--------------------------------------------------------------------------
            | File Upload
            |--------------------------------------------------------------------------
            */

            if ($field['type'] === 'file') {

                $uploader = new FileUploadService();

                $value = $uploader->upload(
                    $files[$key] ?? [],
                    $field
                );

                if (is_wp_error($value)) {
                    return $value;
                }
            }
            /*
            |--------------------------------------------------------------------------
            | Field Sanitize
            |--------------------------------------------------------------------------
            */

            $clean[$key] = $fieldObject->sanitize(
                $value
            );

        }

        return $clean;
    }

    /* -----------------------------------------------------------------
    | Store Entry
    |-----------------------------------------------------------------*/

    /**
     * Store submitted entry.
     *
     * @param int   $form_id Form ID.
     * @param array $fields  Builder fields.
     * @param array $request Submitted data.
     * @param array $files   Uploaded files.
     *
     * @return int|\WP_Error
     */
    public function store_entry(
        int $form_id,
        array $fields,
        array $request,
        array $files = []
    ) {

        $validated = $this->validate(
            $form_id,
            $fields,
            $request,
            $files
        );

        if (is_wp_error($validated)) {
            return $validated;
        }

        $clean = $this->sanitize_fields(
            $fields,
            $request,
            $files
        );

        if (is_wp_error($clean)) {
            return $clean;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Entry
        |--------------------------------------------------------------------------
        */

        $entry_id = $this->repository->create(
            [
                'form_id' => $form_id,
                'user_id' => get_current_user_id(),
                'status' => 'new',
                'ip' => sanitize_text_field(
                    wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')
                ),
                'browser' => sanitize_text_field(
                    wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')
                ),
                'referer' => esc_url_raw(
                    wp_unslash($_SERVER['HTTP_REFERER'] ?? '')
                ),
            ]
        );

        if (!$entry_id) {

            return new \WP_Error(
                'entry_failed',
                __('Unable to create entry.', 'formvexa-form-builder')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Entry Meta
        |--------------------------------------------------------------------------
        */

        foreach ($clean as $key => $value) {

            $saved = $this->meta->create(
                $entry_id,
                $key,
                $value
            );

            if (!$saved) {

                $this->repository->delete($entry_id);

                return new \WP_Error(
                    'meta_failed',
                    __('Unable to save entry fields.', 'formvexa-form-builder')
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Fire Hook
        |--------------------------------------------------------------------------
        */

        do_action(
            'ndfb_after_entry_created',
            $entry_id,
            $form_id,
            $clean
        );

        /*
        |--------------------------------------------------------------------------
        | Send Emails
        |--------------------------------------------------------------------------
        */

        try {

            $mail = new MailService();

            $form = new FormService(
                new \formvexa\Repository\FormRepository($GLOBALS['wpdb']),
                new \formvexa\Repository\MetaRepository($GLOBALS['wpdb'])
            );

            $formData = $form->find($form_id);

            if ($formData) {
                $clean['form_title'] = $formData->title;
            }
            $mail->send(
                $form_id,
                $entry_id,
                $clean
            );

        } catch (\Throwable $e) {

        }

        return (int) $entry_id;
    }

    /* -----------------------------------------------------------------
    | Meta Helpers
    |-----------------------------------------------------------------*/

    /**
     * Get single field value.
     *
     * @param int    $entry_id
     * @param string $field_key
     *
     * @return mixed
     */
    public function get_field(
        int $entry_id,
        string $field_key
    ) {

        return $this->meta->get(
            $entry_id,
            $field_key
        );
    }

    /**
     * Update field.
     *
     * @param int    $entry_id
     * @param string $field_key
     * @param mixed  $value
     *
     * @return bool
     */
    public function update_field(
        int $entry_id,
        string $field_key,
        $value
    ): bool {

        return $this->meta->upsert(
            $entry_id,
            $field_key,
            $value
        );
    }

    /**
     * Delete field.
     *
     * @param int    $entry_id
     * @param string $field_key
     *
     * @return bool
     */
    public function delete_field(
        int $entry_id,
        string $field_key
    ): bool {

        return $this->meta->delete(
            $entry_id,
            $field_key
        );
    }

    /**
     * Delete all entry meta.
     *
     * @param int $entry_id
     *
     * @return bool
     */
    public function delete_meta(
        int $entry_id
    ): bool {

        return $this->meta->delete_by_entry(
            $entry_id
        );
    }

    /* -----------------------------------------------------------------
    | Repository Helpers
    |-----------------------------------------------------------------*/

    /**
     * Repository name.
     *
     * @return string
     */
    public function repository(): string
    {
        return $this->repository->name();
    }

    /**
     * Latest entry.
     *
     * @return object|null
     */
    public function latest(): ?object
    {
        return $this->repository->latest();
    }

    /**
     * Recent entries.
     *
     * @param int $limit
     *
     * @return array
     */
    public function recent(
        int $limit = 10
    ): array {

        return $this->repository->recent(
            $limit
        );
    }

    /**
     * Total pages.
     *
     * @param int $per_page
     *
     * @return int
     */
    public function total_pages(
        int $per_page = 20
    ): int {

        $per_page = max(
            1,
            absint($per_page)
        );

        return (int) ceil(
            $this->repository->count() / $per_page
        );
    }

    /**
     * Truncate repository.
     *
     * @return bool
     */
    public function truncate(): bool
    {
        return $this->repository->truncate();
    }

    public function get_filtered(
        int $page,
        int $per_page,
        int $form_id,
        string $search
    ): array {

        return [
            'items' => $this->repository->paginate_filtered(
                $page,
                $per_page,
                $form_id,
                $search
            ),
            'total' => $this->repository->count_filtered(
                $form_id,
                $search
            )
        ];
    }

    /**
     * Export entries for CSV.
     *
     * @param int $form_id Form ID.
     *
     * @return array
     */
    public function export_csv(int $form_id): array
    {
        return $this->repository->export_csv(
            $form_id
        );
    }
}