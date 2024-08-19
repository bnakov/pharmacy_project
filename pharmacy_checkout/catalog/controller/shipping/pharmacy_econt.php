<?php

namespace Opencart\Catalog\Controller\Extension\PharmacyCheckout\Shipping;

use Opencart\System\Engine\Controller;

class PharmacyEcont extends Controller {

	public function index(): string {

		// load language
		$this->load->language('extension/pharmacy_checkout/shipping/pharmacy_econt');
		$data['language'] = $this->config->get('config_language');

		// econt environment url
		$data['shipping_pharmacy_econt_env_mode'] = $this->config->get('shipping_pharmacy_econt_env_mode');
		// econt delivery connection code
		$data['shipping_pharmacy_econt_connection_code'] = $this->config->get('shipping_pharmacy_econt_connection_code');

		$connection_data = explode('@', $data['shipping_pharmacy_econt_connection_code']);
		$shop_id = !empty($connection_data[0]) ? $connection_data[0] : 0;

		// ИМПЛЕМЕНТИРАНЕ НА ФОРМАТА ЗА ДОСТАВКА
		// параметри за конфигуриране на формата за изчисляване на цена за доставка
		$shippment_calc_url_params = [
			// Задължителни параметри:
			'id_shop' => $shop_id, // ID на електронния магазин
			'order_total' => $this->cart->getTotal(), // Стойност на поръчката (количество за наложен платеж по пратката)
			'order_currency' => isset($this->session->data['currency']) ? $this->session->data['currency'] : 'BGN', // валута на наложения платеж
			'order_weight' => ($this->cart->getWeight() > 0) ? $this->cart->getWeight() : 1.99, // общо тегло на пратката
		];

		// Незадължителни параметри 
		// (попълнените параметри ще запълнят автоматично полетата във формата за изчисляване на цена)
		if (isset($this->session->data['shipping_address']['address_id'])) {

			$shippment_calc_url_params['customer_name'] = $this->session->data['shipping_address']['firstname'] . ' ' . $this->session->data['shipping_address']['lastname']; // лице за доставка
			$shippment_calc_url_params['customer_company'] = $this->session->data['shipping_address']['company'];  // име на фирма

			$shippment_calc_url_params['customer_country'] = $this->session->data['shipping_address']['iso_code_3'];  // държава
			$shippment_calc_url_params['customer_city_name'] = $this->session->data['shipping_address']['city'];  // град
			$shippment_calc_url_params['customer_post_code'] = $this->session->data['shipping_address']['postcode'];  // пк
			$shippment_calc_url_params['customer_address'] = $this->session->data['shipping_address']['address_1'] . ' ' . $this->session->data['shipping_address']['address_2'];  // адрес

		}

		if (isset($this->session->data['customer'])) {
			$shippment_calc_url_params['customer_phone'] = !empty($this->customer->getTelephone()) ? $this->customer->getTelephone() : ''; // телефон 
			$shippment_calc_url_params['customer_email'] = !empty($this->customer->getEmail()) ? $this->customer->getEmail() : ''; // имейл
		}

		$shippment_calc_url_params['ignore_history'] = true; 
		$shippment_calc_url_params['default_css'] = true;

		$data['shippment_calc_url'] = $data['shipping_pharmacy_econt_env_mode'] . '/customer_info.php?' . http_build_query($shippment_calc_url_params, '', '&');

		// echo "<pre>";
		// var_dump($data['shippment_calc_url']);
		// echo "</pre>";
		// die();

		// return html part
		return $this->load->view('extension/pharmacy_checkout/shipping/pharmacy_econt', $data);

	}

	public function confirm(): void {
		$this->response->setOutput($this->index());
	}

}
