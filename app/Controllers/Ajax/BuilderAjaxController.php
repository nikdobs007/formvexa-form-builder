<?php

namespace FormNova\Controllers\Ajax;

defined('ABSPATH') || exit;

use FormNova\Services\FormService;

final class BuilderAjaxController
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
	 * @param FormService $service Form service instance.
	 */
	public function __construct(FormService $service)
	{
		$this->service = $service;
	}

	/**
	 * Register AJAX actions.
	 *
	 * @return void
	 */
	public function register(): void
	{
		add_action('wp_ajax_ndfb_save_form', array($this, 'save_form'));
	}

	/**
	 * Save form builder state.
	 *
	 * @return void
	 */
	public function save_form(): void
	{

		check_ajax_referer('ndfb_nonce', 'nonce');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				array(
					'message' => __('Unauthorized', 'formnova-form-builder'),
				)
			);
		}

		$form_id = isset($_POST['form_id'])
			? absint($_POST['form_id'])
			: 0;

		$title = isset($_POST['title'])
			? sanitize_text_field(wp_unslash($_POST['title']))
			: 'Untitled';

		// JSON payload. Unslash before decoding.
		$builder_raw = isset($_POST['builder'])
			? wp_unslash($_POST['builder'])
			: '';

		$decoded = json_decode($builder_raw, true);

		if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
			wp_send_json_error(
				array(
					'message' => __('Invalid builder JSON', 'formnova-form-builder'),
				)
			);
		}

		$builder = isset($decoded['builder']) && is_array($decoded['builder'])
			? $decoded['builder']
			: [];

		$settings = isset($decoded['settings']) && is_array($decoded['settings'])
			? $decoded['settings']
			: [];

		$form_id = $this->service->save(
			$form_id,
			array(
				'title' => $title,
				'slug' => sanitize_title($title),
				'status' => 'draft',
			),
			$builder,
			$settings
		);

		$redirect = wp_nonce_url(
			admin_url(
				'admin.php?page=formnova-builder&id=' . $form_id
			),
			'ndfb_edit_form_' . $form_id
		);

		wp_send_json_success(
			[
				'form_id' => $form_id,
				'redirect' => html_entity_decode($redirect, ENT_QUOTES, 'UTF-8'),
			]
		);
	}
}