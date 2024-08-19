<?php

namespace Opencart\Admin\Controller\Extension\PharmacySpecialProducts\Module;

use \Opencart\System\Engine\Controller;

class PharmacySpecialProducts extends Controller {

	public function index(): void {

		//
		// module language
		$this->load->language('extension/pharmacy_special_products/module/pharmacy_special_products');

		//
		// breadcrumbs and document title
		$this->document->setTitle($this->language->get('heading_title'));
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/pharmacy_special_products/module/pharmacy_special_products', 'user_token=' . $this->session->data['user_token'])
		];

		//
		// special products module data
		$data['module_data'] = !empty($this->config->get('module_pharmacy_special_products')) ? $this->config->get('module_pharmacy_special_products') : array();
		$data['status'] = $this->config->get('module_pharmacy_special_products_status');
		$data['products_per_page'] = (isset($data['module_data']['products']) ? $data['module_data']['products'] : 8);
		$data["axis"] = (isset($data['module_data']['axis']) ? $data['module_data']['axis'] : 'vertical');
		$data["width"] = (isset($data['module_data']['width']) ? $data['module_data']['width'] : 200);
		$data["height"] = (isset($data['module_data']['height']) ? $data['module_data']['height'] : 200);

		//
		// oc data
		$data['save'] = $this->url->link('extension/pharmacy_special_products/module/pharmacy_special_products.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');		
		$data['user_token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		//
		// html output
		$this->response->setOutput($this->load->view('extension/pharmacy_special_products/module/pharmacy_special_products', $data));

	}

	public function save(): void {

		$this->load->language('extension/pharmacy_special_products/module/pharmacy_special_products');

		$json = [];

		if ( !$this->user->hasPermission('modify', 'extension/pharmacy_special_products/module/pharmacy_special_products') ) {
			$json['error'] = $this->language->get('error_permission');
		}

		if ( !$json ) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_pharmacy_special_products', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
