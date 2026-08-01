<?php

namespace formvexa\Providers;

defined('ABSPATH') || exit;

use formvexa\Core\Shortcodes;

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