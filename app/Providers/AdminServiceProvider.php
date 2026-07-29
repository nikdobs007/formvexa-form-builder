<?php

namespace FormNova\Providers;

defined('ABSPATH') || exit;

use FormNova\Bootstrap\ServiceProvider;
use FormNova\Core\AdminMenu;
use FormNova\Core\Assets;

final class AdminServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        (new AdminMenu())->register();
        (new Assets())->register();
    }
}