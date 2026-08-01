<?php
/**
 * Service Interface
 *
 * @package formvexa
 */

namespace formvexa\Contracts;

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