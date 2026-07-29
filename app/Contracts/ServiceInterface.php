<?php
/**
 * Service Interface
 *
 * @package FormNova
 */

namespace FormNova\Contracts;

defined('ABSPATH') || exit;

/**
 * Base service contract.
 */
interface ServiceInterface
{

    /**
     * Register service hooks / bindings.
     *
     * @return void
     */
    public function register(): void;
}