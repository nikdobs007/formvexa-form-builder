<?php

namespace FormNova\Core;

defined('ABSPATH') || exit;

use FormNova\Bootstrap\Application;

final class Plugin
{

    public static function boot(): void
    {
        Application::boot();
    }
}