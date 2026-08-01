<?php

namespace formvexa\Providers;

defined('ABSPATH') || exit;

use formvexa\Bootstrap\ServiceProvider;
use formvexa\Core\AdminMenu;
use formvexa\Core\Assets;

final class AdminServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        (new AdminMenu())->register();
        (new Assets())->register();
    }
}