<?php
/**
 * Form Service.
 *
 * Acts as the Facade between Controllers and Repositories.
 *
 * @package formvexa
 */

namespace formvexa\Services;

defined('ABSPATH') || exit;

use formvexa\Repository\FormRepository;
use formvexa\Repository\MetaRepository;
use formvexa\Repository\EntryRepository;

/**
 * Handles all Form business logic.
 */
final class FormService
{
    /**
     * Form repository.
     *
     * @var FormRepository
     */
    private FormRepository $forms;

    /**
     * Meta repository.
     *
     * @var MetaRepository
     */
    private MetaRepository $meta;

    private EntryRepository $repository;

    /**
     * Constructor.
     *
     * @param FormRepository $forms Form repository.
     * @param MetaRepository $meta  Meta repository.
     */
    public function __construct(
        FormRepository $forms,
        MetaRepository $meta
    ) {
        $this->forms = $forms;
        $this->meta = $meta;
    }

    /* -----------------------------------------------------------------
     | Read Operations
     |-----------------------------------------------------------------*/

    /**
     * Find form by ID.
     *
     * Automatically attaches builder meta.
     *
     * @param int $id Form ID.
     *
     * @return object|null
     */
    public function find(int $id): ?object
    {
        $form = $this->forms->find($id);

        if (!$form) {
            return null;
        }

        $form->builder = $this->meta->get(
            $id,
            'builder'
        );

        $form->settings = $this->meta->get(
            $id,
            'settings'
        );

        return $form;
    }

    /**
     * Find form by slug.
     *
     * @param string $slug Form slug.
     *
     * @return object|null
     */
    public function findBySlug(string $slug): ?object
    {
        $form = $this->forms->find_by_slug($slug);

        if (!$form) {
            return null;
        }

        $form->builder = $this->meta->get(
            (int) $form->id,
            'builder'
        );

        $form->settings = $this->meta->get(
            (int) $form->id,
            'settings'
        );

        return $form;
    }

    /**
     * Get all forms.
     *
     * @param array $args Query arguments.
     *
     * @return array
     */
    public function all(array $args = []): array
    {
        return $this->forms->all($args);
    }

    /**
     * Get paginated forms.
     *
     * @param int    $page     Current page.
     * @param int    $perPage  Records per page.
     * @param string $orderby  Order by column.
     * @param string $order    ASC|DESC.
     *
     * @return array
     */
    public function paginate(
        int $page = 1,
        int $perPage = 20,
        string $orderby = 'id',
        string $order = 'DESC'
    ): array {

        return $this->forms->paginate(
            $page,
            $perPage,
            $orderby,
            $order
        );
    }

    /**
     * Search forms.
     *
     * @param string $keyword Search keyword.
     * @param int    $page    Current page.
     * @param int    $perPage Records per page.
     *
     * @return array
     */
    public function search(
        string $keyword,
        int $page = 1,
        int $perPage = 20
    ): array {

        return $this->forms->search(
            $keyword,
            $page,
            $perPage
        );
    }

    /**
     * Count forms.
     *
     * @param string $search Optional search term.
     *
     * @return int
     */
    public function count(string $search = ''): int
    {
        return $this->forms->count($search);
    }

    /**
     * Count forms by status.
     *
     * @param string $status Form status.
     *
     * @return int
     */
    public function countByStatus(
        string $status
    ): int {

        return $this->forms->count_by_status(
            $status
        );
    }

    /**
     * Check whether form exists.
     *
     * @param int $id Form ID.
     *
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->forms->exists($id);
    }

    /**
     * Check slug already exists.
     *
     * @param string   $slug      Slug.
     * @param int|null $excludeId Exclude ID.
     *
     * @return bool
     */
    public function slugExists(
        string $slug,
        ?int $excludeId = null
    ): bool {

        return $this->forms->slug_exists(
            $slug,
            $excludeId
        );
    }

    /**
     * Get latest form.
     *
     * @return object|null
     */
    public function latest(): ?object
    {
        return $this->forms->latest();
    }

    /**
     * Get recent forms.
     *
     * @param int $limit Limit.
     *
     * @return array
     */
    public function recent(
        int $limit = 10
    ): array {

        return $this->forms->recent(
            $limit
        );
    }

    /**
     * Dropdown list.
     *
     * @return array
     */
    public function dropdown(): array
    {
        return $this->forms->dropdown();
    }

    /**
     * Total pages.
     *
     * @param int $perPage Per page.
     *
     * @return int
     */
    public function totalPages(
        int $perPage = 20
    ): int {

        return $this->forms->total_pages(
            $perPage
        );
    }

    /**
     * Repository name.
     *
     * @return string
     */
    public function repositoryName(): string
    {
        return $this->forms->name();
    }

    /* -----------------------------------------------------------------
 | CRUD Operations
 |-----------------------------------------------------------------*/

    /**
     * Create form.
     *
     * @param array $data Form data.
     *
     * @return int
     */
    public function create(array $data): int
    {
        $data = $this->validate($data);

        $data['slug'] = $this->generateUniqueSlug(
            $data['slug'] ?? $data['title']
        );

        $formId = $this->forms->create($data);

        if (!$formId) {
            return 0;
        }

        do_action(
            'ndfb_after_form_created',
            $formId,
            $data
        );

        return $formId;
    }

    /**
     * Update form.
     *
     * @param int   $id   Form ID.
     * @param array $data Form data.
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

        $data = $this->validate(
            $data,
            true
        );

        if (!empty($data['slug'])) {

            $data['slug'] = $this->generateUniqueSlug(
                $data['slug'],
                $id
            );
        }

        $updated = $this->forms->update(
            $id,
            $data
        );

        if ($updated) {

            do_action(
                'ndfb_after_form_updated',
                $id,
                $data
            );
        }

        return $updated;
    }

    /**
     * Delete form.
     *
     * @param int $id Form ID.
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        if (!$this->exists($id)) {
            return false;
        }

        $this->meta->delete_by_form($id);

        return $this->forms->delete($id);
    }

    /**
     * Bulk delete forms.
     *
     * @param array $ids Form IDs.
     *
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        $deleted = 0;

        foreach ($ids as $id) {

            if ($this->delete((int) $id)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Duplicate form.
     *
     * @param int $id Form ID.
     *
     * @return int
     */
    public function duplicate(int $id): int
    {
        return $this->forms->duplicate($id);
    }

    /* -----------------------------------------------------------------
     | Builder Save
     |-----------------------------------------------------------------*/

    /**
     * Save builder.
     *
     * Creates or updates form + builder meta.
     *
     * @param int   $formId  Form ID.
     * @param array $form    Form data.
     * @param array $builder Builder state.
     *
     * @return int
     */
    public function save(
        int $formId,
        array $form,
        array $builder,
        array $settings = []
    ): int {

        $form = $this->validate($form);

        $builder = $this->sanitize_builder($builder);
        $settings = $this->sanitize_settings($settings);

        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        if ($formId === 0) {

            $form['slug'] = $this->generateUniqueSlug(
                $form['slug'] ?? $form['title']
            );

            $formId = $this->forms->create($form);

            if (!$formId) {
                return 0;
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            if (!empty($form['slug'])) {

                $form['slug'] = $this->generateUniqueSlug(
                    $form['slug'],
                    $formId
                );
            }

            $this->forms->update(
                $formId,
                $form
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Builder Meta
        |--------------------------------------------------------------------------
        */

        $this->meta->upsert(
            $formId,
            'builder',
            [
                'builder' => $builder
            ]
        );

        $this->meta->upsert(
            $formId,
            'settings',
            $settings
        );

        do_action(
            'ndfb_after_form_saved',
            $formId,
            $builder,
            $settings
        );

        return $formId;
    }

    /* -----------------------------------------------------------------
     | Validation
     |-----------------------------------------------------------------*/

    /**
     * Validate form data.
     *
     * @param array $data      Form data.
     * @param bool  $updating  Update mode.
     *
     * @return array
     */
    private function validate(
        array $data,
        bool $updating = false
    ): array {

        if (!$updating && empty($data['title'])) {
            $data['title'] = __('Untitled Form', 'formvexa-form-builder');
        }

        $data['title'] = sanitize_text_field(
            $data['title'] ?? ''
        );

        $data['slug'] = sanitize_title(
            $data['slug'] ?? $data['title']
        );

        $data['status'] = sanitize_key(
            $data['status'] ?? 'draft'
        );

        return $data;
    }

    /**
     * Generate unique slug.
     *
     * @param string   $slug
     * @param int|null $excludeId
     *
     * @return string
     */
    private function generateUniqueSlug(
        string $slug,
        ?int $excludeId = null
    ): string {

        $slug = sanitize_title($slug);

        $base = $slug;

        $i = 1;

        while (
            $this->forms->slug_exists(
                $slug,
                $excludeId
            )
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /* -----------------------------------------------------------------
 | Meta Operations
 |-----------------------------------------------------------------*/

    /**
     * Get meta value.
     *
     * @param int    $formId Form ID.
     * @param string $key    Meta key.
     *
     * @return mixed
     */
    public function getMeta(
        int $formId,
        string $key
    ) {
        return $this->meta->get(
            $formId,
            $key
        );
    }

    /**
     * Get complete builder data.
     *
     * @param int $formId
     *
     * @return array
     */
    public function getBuilder(int $formId): array
    {
        $builder = $this->meta->get(
            $formId,
            'builder'
        );

        return is_array($builder)
            ? $builder
            : [];
    }

    /**
     * Save only builder.
     *
     * @param int   $formId
     * @param array $builder
     *
     * @return bool
     */
    public function saveBuilder(
        int $formId,
        array $builder
    ): bool {

        return $this->meta->upsert(
            $formId,
            'builder',
            [
                'builder' => $builder
            ]
        );
    }

    /**
     * Set meta.
     *
     * @param int    $formId
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function setMeta(
        int $formId,
        string $key,
        $value
    ): bool {

        return $this->meta->upsert(
            $formId,
            $key,
            $value
        );
    }

    /**
     * Alias of setMeta().
     *
     * @param int    $formId
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function updateMeta(
        int $formId,
        string $key,
        $value
    ): bool {

        return $this->setMeta(
            $formId,
            $key,
            $value
        );
    }

    /**
     * Upsert meta.
     *
     * @param int    $formId
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function upsertMeta(
        int $formId,
        string $key,
        $value
    ): bool {

        return $this->meta->upsert(
            $formId,
            $key,
            $value
        );
    }

    /**
     * Delete meta record.
     *
     * @param int $metaId
     *
     * @return bool
     */
    public function deleteMeta(
        int $metaId
    ): bool {

        return $this->meta->delete(
            $metaId
        );
    }

    /**
     * Delete all meta for form.
     *
     * @param int $formId
     *
     * @return bool
     */
    public function deleteMetaByForm(
        int $formId
    ): bool {

        return $this->meta->delete_by_form(
            $formId
        );
    }

    /**
     * Get all meta.
     *
     * @param array $args
     *
     * @return array
     */
    public function allMeta(
        array $args = []
    ): array {

        return $this->meta->all(
            $args
        );
    }

    /* -----------------------------------------------------------------
     | Repository Helper Methods
     |-----------------------------------------------------------------*/

    /**
     * Get forms by status.
     *
     * @param string $status
     *
     * @return array
     */
    public function getByStatus(
        string $status
    ): array {

        return $this->forms->get_by_status(
            $status
        );
    }

    /**
     * Search count.
     *
     * @param string $keyword
     *
     * @return int
     */
    public function searchCount(
        string $keyword
    ): int {

        return $this->forms->search_count(
            $keyword
        );
    }

    /**
     * Duplicate form.
     *
     * @param int $formId
     *
     * @return int
     */
    public function duplicateForm(
        int $formId
    ): int {

        return $this->forms->duplicate(
            $formId
        );
    }

    /**
     * Get latest form.
     *
     * @return object|null
     */
    public function latestForm(): ?object
    {
        return $this->forms->latest();
    }

    /**
     * Get recent forms.
     *
     * @param int $limit
     *
     * @return array
     */
    public function recentForms(
        int $limit = 10
    ): array {

        return $this->forms->recent(
            $limit
        );
    }

    /**
     * Get dropdown options.
     *
     * @return array
     */
    public function dropdownOptions(): array
    {
        return $this->forms->dropdown();
    }

    /**
     * Repository name.
     *
     * @return string
     */
    public function repository(): string
    {
        return $this->forms->name();
    }

    /**
     * Truncate forms and meta.
     *
     * @return bool
     */
    public function truncate(): bool
    {
        $this->meta->truncate();

        return $this->forms->truncate();
    }

    public function get_paginated(
        int $page,
        int $per_page,
        string $search = ''
    ): array {

        if ($search !== '') {
            return [
                'items' => $this->forms->search(
                    $search,
                    $page,
                    $per_page
                ),
                'total' => $this->forms->search_count($search),
            ];
        }

        return [
            'items' => $this->forms->paginate(
                $page,
                $per_page
            ),
            'total' => $this->forms->count(),
        ];
    }

    /**
     * Get builder JSON for a form.
     *
     * @param int $form_id Form ID.
     *
     * @return array
     */
    public function get_builder(int $form_id): array
    {
        $builder = $this->meta->get($form_id, 'builder');

        if (empty($builder) || !is_array($builder)) {
            return [];
        }

        return $builder['builder'] ?? [];
    }

    /**
     * Get builder fields.
     *
     * Returns builder array only.
     *
     * @param int $form_id
     *
     * @return array
     */
    public function get_fields(int $form_id): array
    {
        $builder = $this->meta->get(
            $form_id,
            'builder'
        );

        if (
            !is_array($builder)
            || empty($builder['builder'])
            || !is_array($builder['builder'])
        ) {
            return [];
        }

        return $builder['builder'];
    }

    /**
     * Get form settings.
     *
     * @param int $formId
     *
     * @return array
     */
    public function getSettings(int $formId): array
    {
        $settings = $this->meta->get(
            $formId,
            'settings'
        );

        return is_array($settings)
            ? $settings
            : [];
    }

    /**
     * Save form settings.
     *
     * @param int   $formId
     * @param array $settings
     *
     * @return bool
     */
    public function saveSettings(
        int $formId,
        array $settings
    ): bool {

        return $this->meta->upsert(
            $formId,
            'settings',
            $settings
        );
    }

    /**
     * Recursively sanitize builder data.
     *
     * @param array $builder
     *
     * @return array
     */
    private function sanitize_builder(array $builder): array
    {
        foreach ($builder as $key => $value) {

            if (is_array($value)) {
                $builder[$key] = $this->sanitize_builder($value);
                continue;
            }

            if (is_bool($value) || is_numeric($value) || $value === null) {
                $builder[$key] = $value;
                continue;
            }

            $builder[$key] = sanitize_text_field((string) $value);
        }

        return $builder;
    }

    /**
     * Recursively sanitize settings data.
     *
     * @param mixed $settings Settings array.
     *
     * @return mixed
     */
    private function sanitize_settings($settings)
    {
        if (!is_array($settings)) {
            return is_string($settings)
                ? sanitize_text_field($settings)
                : $settings;
        }

        foreach ($settings as $key => $value) {

            if (is_array($value)) {
                $settings[$key] = $this->sanitize_settings($value);
                continue;
            }

            if (
                is_bool($value) ||
                is_numeric($value) ||
                $value === null
            ) {
                $settings[$key] = $value;
                continue;
            }

            if (!is_string($value)) {
                $settings[$key] = $value;
                continue;
            }

            switch ($key) {

                case 'email':
                case 'from_email':
                case 'reply_to':
                case 'admin_to':
                    $settings[$key] = sanitize_email($value);
                    break;

                case 'redirect_url':
                case 'success_url':
                case 'url':
                    $settings[$key] = esc_url_raw($value);
                    break;

                case 'admin_message':
                case 'user_message':
                case 'success_message':
                case 'error_message':
                    $settings[$key] = wp_kses_post($value);
                    break;

                default:
                    $settings[$key] = sanitize_text_field($value);
                    break;
            }
        }

        return $settings;
    }

    /**
     * Default builder fields.
     *
     * @return array
     */
    public static function default_builder(): array
    {
        return [

            [
                'id' => uniqid('field_'),
                'type' => 'text',
                'label' => 'Name',
                'name' => 'name',
                'required' => true,
            ],

            [
                'id' => uniqid('field_'),
                'type' => 'email',
                'label' => 'Email',
                'name' => 'email',
                'required' => true,
            ],

            [
                'id' => uniqid('field_'),
                'type' => 'text',
                'label' => 'Subject',
                'name' => 'subject',
                'required' => true,
            ],

            [
                'id' => uniqid('field_'),
                'type' => 'textarea',
                'label' => 'Message',
                'name' => 'message',
                'rows' => 5,
                'required' => true,
            ],

        ];
    }
}