<?php

namespace formvexa\Bootstrap;

defined('ABSPATH') || exit;

/**
 * Base service provider.
 */
abstract class ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Boot services.
     */
    public function boot(): void
    {
    }
}