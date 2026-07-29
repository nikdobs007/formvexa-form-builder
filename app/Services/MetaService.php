<?php
/**
 * Meta service layer.
 *
 * @package FormNova
 */

namespace FormNova\Services;

defined('ABSPATH') || exit;

use FormNova\Repository\MetaRepository;

/**
 * Handles business logic for form meta.
 */
final class MetaService
{

    /**
     * Meta repository instance.
     *
     * @var MetaRepository
     */
    private MetaRepository $repository;

    /**
     * Constructor.
     *
     * @param MetaRepository $repository Meta repository.
     */
    public function __construct(MetaRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get meta value.
     *
     * @param int    $form_id  Form ID.
     * @param string $key      Meta key.
     *
     * @return mixed
     */
    public function get(int $form_id, string $key)
    {

        $value = $this->repository->get($form_id, $key);

        if (null === $value) {
            return null;
        }

        $decoded = json_decode($value, true);

        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
    }

    /**
     * Set meta value (create or update).
     *
     * @param int    $form_id  Form ID.
     * @param string $key      Meta key.
     * @param mixed  $value    Meta value.
     *
     * @return bool
     */
    public function set(int $form_id, string $key, $value): bool
    {

        return $this->repository->update($form_id, $key, $value);
    }

    /**
     * Delete meta.
     *
     * @param int    $form_id Form ID.
     * @param string $key     Meta key.
     *
     * @return bool
     */
    public function delete(int $form_id, string $key): bool
    {

        return $this->repository->delete($form_id, $key);
    }

    /**
     * Get decoded builder JSON safely.
     *
     * @param int $form_id Form ID.
     *
     * @return array
     */
    public function get_builder(int $form_id): array
    {

        $builder = $this->get($form_id, 'builder');

        if (empty($builder) || !is_array($builder)) {
            return [];
        }

        return $builder;
    }

    /**
     * Get settings JSON.
     *
     * @param int $form_id Form ID.
     *
     * @return array
     */
    public function get_settings(int $form_id): array
    {

        $settings = $this->get($form_id, 'settings');

        if (empty($settings) || !is_array($settings)) {
            return [];
        }

        return $settings;
    }
}