<?php

namespace Opencart\Admin\Controller\Extension\PharmacyCheckout\Shipping;

use Opencart\System\Engine\Controller;

class PharmacyEcont extends Controller {

	public function index(): void {

		$this->load->language('extension/pharmacy_checkout/shipping/pharmacy_econt');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/pharmacy_checkout/shipping/pharmacy_econt', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/pharmacy_checkout/shipping/pharmacy_econt.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping');

		$this->load->model('localisation/geo_zone');

		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();

		foreach ($geo_zones as $geo_zone) {
			$data['shipping_pharmacy_econt_geo_zone_rate'][$geo_zone['geo_zone_id']] = $this->config->get('shipping_pharmacy_econt_' . $geo_zone['geo_zone_id'] . '_rate');
			$data['shipping_pharmacy_econt_geo_zone_status'][$geo_zone['geo_zone_id']] = $this->config->get('shipping_pharmacy_econt_' . $geo_zone['geo_zone_id'] . '_status');
		}

		$data['geo_zones'] = $geo_zones;

		$data['shipping_pharmacy_econt_tax_class_id'] = $this->config->get('shipping_pharmacy_econt_tax_class_id');

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$data['shipping_pharmacy_econt_status'] = $this->config->get('shipping_pharmacy_econt_status');
		$data['shipping_pharmacy_econt_sort_order'] = $this->config->get('shipping_pharmacy_econt_sort_order');

		$data['shipping_pharmacy_econt_env_prod'] = $this->config->get('shipping_pharmacy_econt_env_prod'); // my code
		$data['shipping_pharmacy_econt_env_test'] = $this->config->get('shipping_pharmacy_econt_env_test'); // my code
		$data['shipping_pharmacy_econt_env_mode'] = $this->config->get('shipping_pharmacy_econt_env_mode'); // my code
		$data['shipping_pharmacy_econt_connection_code'] = $this->config->get('shipping_pharmacy_econt_connection_code'); // my code

		$data['shipping_pharmacy_econt_rate_address'] = $this->config->get('shipping_pharmacy_econt_rate_address');
		$data['shipping_pharmacy_econt_rate_office'] = $this->config->get('shipping_pharmacy_econt_rate_office');
		$data['shipping_pharmacy_econt_geo_zone_id'] = $this->config->get('shipping_pharmacy_econt_geo_zone_id');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/pharmacy_checkout/shipping/pharmacy_econt', $data));
	}

	/**
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/pharmacy_checkout/shipping/pharmacy_econt');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/pharmacy_checkout/shipping/pharmacy_econt')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('shipping_pharmacy_econt', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		if ($this->user->hasPermission('modify', 'extension/pharmacy_checkout/shipping/pharmacy_econt')) {
			$this->load->model('extension/pharmacy_checkout/shipping/pharmacy_econt');
			$this->model_extension_pharmacy_checkout_shipping_pharmacy_econt->install();
		}
	}

}
