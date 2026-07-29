<?php

namespace FormNova\Providers;

defined('ABSPATH') || exit;

use FormNova\Core\Shortcodes;

class FrontendServiceProvider
{
    public function register(): void
    {
        $shortcodes = new Shortcodes();

        $shortcodes->register();
    }

    public function boot(): void
    {
    }
}