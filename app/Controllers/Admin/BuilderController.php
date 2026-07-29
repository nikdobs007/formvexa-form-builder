<?php

namespace FormNova\Controllers\Admin;

defined('ABSPATH') || exit;

use FormNova\Core\View;
use FormNova\Services\FormService;
use FormNova\Services\FieldSchemaService;

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

            $builder = [

                [
                    'id' => uniqid('field_'),
                    'type' => 'text',
                    'label' => 'Name',
                    'name' => 'name',
                    'required' => true,
                ],

                [
                    'id' => uniqid('field_'),
                    'type' => 'email',
                    'label' => 'Email',
                    'name' => 'email',
                    'required' => true,
                ],

                [
                    'id' => uniqid('field_'),
                    'type' => 'text',
                    'label' => 'Subject',
                    'name' => 'subject',
                    'required' => true,
                ],

                [
                    'id' => uniqid('field_'),
                    'type' => 'textarea',
                    'label' => 'Message',
                    'name' => 'message',
                    'rows' => 5,
                    'required' => true,
                ],

            ];

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