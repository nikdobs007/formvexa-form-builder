<?php
/**
 * Hook Loader.
 *
 * @package FormNova
 */

namespace FormNova\Bootstrap;

defined('ABSPATH') || exit;

use FormNova\Controllers\Ajax\BuilderAjaxController;
use FormNova\Controllers\Ajax\SubmissionAjaxController;
use FormNova\Providers\AdminServiceProvider;
use FormNova\Providers\FieldServiceProvider;
use FormNova\Providers\FrontendServiceProvider;
use FormNova\Repository\EntryMetaRepository;
use FormNova\Repository\EntryRepository;
use FormNova\Repository\FormRepository;
use FormNova\Repository\MetaRepository;
use FormNova\Services\EntryService;
use FormNova\Services\FormService;
use FormNova\Services\CaptchaService;

final class HookLoader
{
    /**
     * Register plugin hooks.
     *
     * @return void
     */
    public function register(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Providers
        |--------------------------------------------------------------------------
        */

        (new AdminServiceProvider())->register();

        add_action(
            'init',
            static function (): void {
                (new FieldServiceProvider())->boot();
            }
        );

        (new FrontendServiceProvider())->register();

        /*
        |--------------------------------------------------------------------------
        | Dependencies
        |--------------------------------------------------------------------------
        */

        global $wpdb;

        /*
        |--------------------------------------------------------------------------
        | Repositories
        |--------------------------------------------------------------------------
        */

        $formRepository = new FormRepository($wpdb);

        $metaRepository = new MetaRepository($wpdb);

        $entryRepository = new EntryRepository($wpdb);

        $entryMetaRepository = new EntryMetaRepository($wpdb);

        /*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

        $formService = new FormService(
            $formRepository,
            $metaRepository
        );

        $entryService = new EntryService(
            $entryRepository,
            $entryMetaRepository
        );

        $captchaService = new CaptchaService();

        /*
        |--------------------------------------------------------------------------
        | AJAX Controllers
        |--------------------------------------------------------------------------
        */

        (new BuilderAjaxController(
            $formService
        ))->register();

        (new SubmissionAjaxController(
            $entryService,
            $formService,
            $captchaService
        ))->register();
    }
}