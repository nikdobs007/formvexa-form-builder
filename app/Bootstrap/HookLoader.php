<?php
/**
 * Hook Loader.
 *
 * @package formvexa
 */

namespace formvexa\Bootstrap;

defined('ABSPATH') || exit;

use formvexa\Controllers\Ajax\BuilderAjaxController;
use formvexa\Controllers\Ajax\SubmissionAjaxController;
use formvexa\Providers\AdminServiceProvider;
use formvexa\Providers\FieldServiceProvider;
use formvexa\Providers\FrontendServiceProvider;
use formvexa\Repository\EntryMetaRepository;
use formvexa\Repository\EntryRepository;
use formvexa\Repository\FormRepository;
use formvexa\Repository\MetaRepository;
use formvexa\Services\EntryService;
use formvexa\Services\FormService;
use formvexa\Services\CaptchaService;

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