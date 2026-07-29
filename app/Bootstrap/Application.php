<?php

namespace FormNova\Bootstrap;
use FormNova\Database\Installer;

defined('ABSPATH') || exit;

/**
 * Plugin application bootstrap.
 */
final class Application
{

    /**
     * Service providers.
     *
     * @var array<int, ServiceProvider>
     */
    private array $providers = [];

    /**
     * Bootstrap application.
     */
    public static function boot(): void
    {
        $app = new self();

        $app->register_providers();
        $app->boot_providers();

        (new HookLoader())->register();

        Installer::maybe_upgrade();
    }

    /**
     * Register service providers.
     */
    private function register_providers(): void
    {

        foreach ($this->providers as $provider) {
            $provider->register();
        }
    }

    /**
     * Boot service providers.
     */
    private function boot_providers(): void
    {

        foreach ($this->providers as $provider) {
            $provider->boot();
        }
    }
}