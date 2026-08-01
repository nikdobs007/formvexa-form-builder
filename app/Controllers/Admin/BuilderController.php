<?php

namespace formvexa\Controllers\Admin;

defined('ABSPATH') || exit;

use formvexa\Core\View;
use formvexa\Services\FormService;
use formvexa\Services\FieldSchemaService;

final class BuilderController
{
    /**
     * Form service.
     *
     * @var FormService
     */
    private FormService $service;

    /**
     * Constructor.
     *
     * @param FormService $service Form service.
     */
    public function __construct(FormService $service)
    {
        $this->service = $service;
    }

    /**
     * Builder page.
     *
     * @param int $id Form ID.
     *
     * @return void
     */
    public function index(int $id = 0): void
    {
        $form = null;
        $builder = [];
        $settings = [];

        $form = $this->service->find($id);

        if ($id > 0) {

            $form = $this->service->find($id);
            $builder = $this->service->get_builder($id);
            $settings = $this->service->getSettings($id);
        }

        if ($id === 0) {
            $builder = FormService::default_builder();
        }

        $schemas = (new FieldSchemaService())->all();

        if (function_exists('nocache_headers')) {
            \nocache_headers();
        }

        View::render(
            'admin/forms/builder',
            [
                'form_id' => $id,
                'form' => $form,
                'builder' => $builder,
                'settings' => $settings,
                'schemas' => $schemas,
            ]
        );
    }
}