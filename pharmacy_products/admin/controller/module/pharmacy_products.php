<?php

namespace Opencart\Admin\Controller\Extension\PharmacyProducts\Module;

use \Opencart\System\Engine\Controller;

class PharmacyProducts extends Controller {

	public function index(): void {

		//
		// module language
		$this->load->language('extension/pharmacy_products/module/pharmacy_products');

		//
		// module title and breadcrumbs
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
		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/pharmacy_products/module/pharmacy_products', 'user_token=' . $this->session->data['user_token'])
			];
		} else {
			$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/pharmacy_products/module/pharmacy_products', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'])
			];
		}

		//
		// opencart control buttons
		if (!isset($this->request->get['module_id'])) {
			$data['save'] = $this->url->link('extension/pharmacy_products/module/pharmacy_products.save', 'user_token=' . $this->session->data['user_token']);
		} else {
			$data['save'] = $this->url->link('extension/pharmacy_products/module/pharmacy_products.save', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id']);
		}
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		//
		// featured products module data
		$data['featured'] = !empty($this->config->get('module_pharmacy_products_featured')) ? $this->config->get('module_pharmacy_products_featured') : array();
		$data['module_pharmacy_products_status'] = $this->config->get('module_pharmacy_products_status');

		$products = [];
		if (isset($data['featured']["products"]) && is_array($data['featured']['products'])) {
			$products = $data['featured']['products'];
		}

		$this->load->model('catalog/product');
		$data['featured']['products'] = [];
		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$data['featured']['products'][] = [
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				];
			}
		}

		if (!isset($data['featured']['axis'])) {
			$data['featured']['axis'] = "horizontal";
		}

		if (!isset($data['featured']["width"])) {
			$data['featured']["width"] = 200;
		}

		if (!isset($data['featured']["height"])) {
			$data['featured']["height"] = 200;
		}
		
		if (isset($this->request->get['module_id'])) {
			$data['module_id'] = (int)$this->request->get['module_id'];
		} else {
			$data['module_id'] = 0;
		}

		//
		// opencart data
		$data['user_token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		//
		// html output
		$this->response->setOutput($this->load->view('extension/pharmacy_products/module/pharmacy_products', $data));

	}

	public function save(): void {

		$this->load->language('extension/pharmacy_products/module/pharmacy_products');

		$json = [];

		if ( !$this->user->hasPermission('modify', 'extension/pharmacy_products/module/pharmacy_products') ) {
			$json['error'] = $this->language->get('error_permission');
		}

		if ( !$json ) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_pharmacy_products', $this->request->post);
			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}
