<?php

namespace FormNova\Controllers\Admin;

defined('ABSPATH') || exit;

use FormNova\Core\View;
use FormNova\Services\FormService;

/**
 * Forms controller.
 */
final class FormsController
{

    /**
     * Service.
     *
     * @var FormService
     */
    private FormService $service;

    /**
     * Constructor.
     *
     * @param FormService $service Service.
     */
    public function __construct(FormService $service)
    {
        $this->service = $service;
    }

    /**
     * Forms list.
     *
     * @return void
     */
    public function index(): void
    {

        $table = new FormsListTable($this->service);
        $table->prepare_items();

        View::render('admin/forms/index', [
            'table' => $table,
        ]);
    }
}