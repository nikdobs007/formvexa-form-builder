<?php

namespace formvexa\Core;

defined('ABSPATH') || exit;

use formvexa\Bootstrap\Application;

final class Plugin
{

    public static function boot(): void
    {
        Application::boot();
    }
}