<?php

namespace Opencart\Catalog\Model\Extension\PharmacyCheckout\Shipping;

use Opencart\System\Engine\Model;

class PharmacyEcont extends Model {

	public function getQuote(array $address): array {

		$this->load->language('extension/pharmacy_checkout/shipping/pharmacy_econt');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('shipping_pharmacy_econt_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if (!$this->config->get('shipping_pharmacy_econt_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		// dont allow shipping of rx medicines
		$rx = false;
		$products = $this->cart->getProducts();

		if ($products) {

			$this->load->model('extension/pharmacy_theme/catalog/pharmacy_data');

			foreach($products as $product) {

				//
				// get pharmacy data for selected product
				$pharmacy_data = $this->model_extension_pharmacy_theme_catalog_pharmacy_data->getPharmacyDataForProduct($product['product_id']);

				// drug type rx warning
				if ($pharmacy_data['drug_type_group'] == 'rx') {
					$rx = true;
					$data['warning_rx_product'] = $this->language->get('warning_rx_product');
				}
		
			}

		}


		$method_data = [];

		if ($status && $this->cart->hasShipping() && !$rx) {

			// basic params, weight based shipping
			$weight = $this->cart->getWeight();
			$shipping_price = 0.00;
			$shipping_price_cod = 0.00;
			$shipping_price_extra = 0.00;
			$name = $this->language->get('text_econt_delivery');
			$currency = isset($this->session->data['currency']) ? $this->session->data['currency'] : '';
			$delivery = ''; // delivery option: address or office

			// econt cost for a weight based delivery to address
			$cost_address = '';
			$rates_address = explode(',', $this->config->get('shipping_pharmacy_econt_rate_address'));
	
			foreach ($rates_address as $rate) {
				$data = explode(':', $rate);
	
				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost_address = $data[1];
					}
	
					break;
				}
			}

			$weight_based_cost_address = !empty($cost_address) ? (float) $cost_address : 0.00;

			// econt cost for a weight based delivery to office / automat
			$cost_office = '';
			$rates_office = explode(',', $this->config->get('shipping_pharmacy_econt_rate_office'));
	
			foreach ($rates_office as $rate) {
				$data = explode(':', $rate);
	
				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost_office = $data[1];
					}
	
					break;
				}
			}
	
			$weight_based_cost_office = !empty($cost_office) ? (float) $cost_office : 0.00;

			// echo "cost address<pre>";
			// var_dump($weight_based_cost_address);
			// echo "</pre>";

			// echo "cost office<pre>";
			// var_dump($weight_based_cost_office);
			// echo "</pre>";

			// die();

			if (isset($this->session->data['econt_delivery']['customer_info'])) {

				// check delivery option
				if (!empty($this->session->data['econt_delivery']['customer_info']['address'])) {
					$delivery = 'address';
				} elseif (!empty($this->session->data['econt_delivery']['customer_info']['office_code'])) {
					$delivery = 'office';
				} else {
					$delivery = '';
				}

				// econt params and price
				$shipping_price = number_format((float) $this->session->data['econt_delivery']['customer_info']['shipping_price'], 2, '.', '');
				$shipping_price_cod = number_format((float) $this->session->data['econt_delivery']['customer_info']['shipping_price_cod'], 2, '.', '');
				$shipping_price_extra = number_format(((float) $shipping_price_cod - (float) $shipping_price), 2, '.', '');
				$currency = $this->session->data['econt_delivery']['customer_info']['shipping_price_currency_sign'];
				// $name = $this->language->get('text_econt_delivery') . ' - ' . $shipping_price . ' ' . $currency . ' (' . $this->language->get('text_payment_cod_label') . ' ' . $shipping_price_extra . ' ' . $currency . ')';
				$name = $this->language->get('text_econt_delivery');

				// override shipping price (weight based)
				if ($delivery == 'address' && !empty($weight_based_cost_address)) {
					$shipping_price = number_format((float) $weight_based_cost_address, 2, '.', '');
				}

				if ($delivery == 'office' && !empty($weight_based_cost_office)) {
					$shipping_price = number_format((float) $weight_based_cost_office, 2, '.', '');
				}

			}


			//
			// shipping method data
			$quote_data['pharmacy_econt'] = [

				'code'             => 'pharmacy_econt.pharmacy_econt',
				'name'             => $name,
				'tax_class_id'     => $this->config->get('shipping_pharmacy_econt_tax_class_id'),

				'cost'             => $shipping_price,
				'text'             => $this->currency->format($this->tax->calculate($shipping_price, $this->config->get('shipping_pharmacy_econt_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']),

				'econt'            => true,
				'delivery'         => $delivery,
				'cost_address'     => $weight_based_cost_address,
				'cost_office'      => $weight_based_cost_office,

				'cost_cod'         => $shipping_price_cod,
				'text_cod'         => $this->currency->format($this->tax->calculate($shipping_price_cod, $this->config->get('shipping_pharmacy_econt_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']),

				'cost_extra'       => $shipping_price_extra,
				'text_extra'       => $this->currency->format($this->tax->calculate($shipping_price_extra, $this->config->get('shipping_pharmacy_econt_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']),
				'text_extra_label' => $this->language->get('text_payment_cod_label')

			];


			$method_data = [
				'code'       => 'pharmacy_econt',
				'name'       => $this->language->get('heading_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_pharmacy_econt_sort_order'),
				'error'      => false
			];


		}

		return $method_data;
	}


	public function update_econt_shipping(): array {

		$data = array();

		if (isset($this->session->data['econt_delivery']['customer_info'])) {

			// basic params, weight based shipping
			$weight = $this->cart->getWeight();
			$shipping_price = 0.00;
			$shipping_price_cod = 0.00;
			$shipping_price_extra = 0.00;
			$delivery = ''; // delivery option: address or office


			// econt cost for a weight based delivery to address
			$cost_address = '';
			$rates_address = explode(',', $this->config->get('shipping_pharmacy_econt_rate_address'));

			foreach ($rates_address as $rate) {
				$data = explode(':', $rate);

				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost_address = $data[1];
					}

					break;
				}
			}
			$weight_based_cost_address = !empty($cost_address) ? (float) $cost_address : 0.00;


			// econt cost for a weight based delivery to office / automat
			$cost_office = '';
			$rates_office = explode(',', $this->config->get('shipping_pharmacy_econt_rate_office'));

			foreach ($rates_office as $rate) {
				$data = explode(':', $rate);

				if ($data[0] >= $weight) {
					if (isset($data[1])) {
						$cost_office = $data[1];
					}

					break;
				}
			}	
			$weight_based_cost_office = !empty($cost_office) ? (float) $cost_office : 0.00;


			// check delivery option
			if (!empty($this->session->data['econt_delivery']['customer_info']['address'])) {
				$delivery = 'address';
			} elseif (!empty($this->session->data['econt_delivery']['customer_info']['office_code'])) {
				$delivery = 'office';
			} else {
				$delivery = '';
			}


			// econt params and price
			$shipping_price = (float) $this->session->data['econt_delivery']['customer_info']['shipping_price'];
			$shipping_price_cod = (float) $this->session->data['econt_delivery']['customer_info']['shipping_price_cod'];
			$shipping_price_extra = (float) $shipping_price_cod - (float) $shipping_price;


			//
			// update econt shipping method
			if (isset($this->session->data['shipping_methods']['pharmacy_econt'])) {

				//
				// override shipping price (weight based)
				if ($delivery == 'address' && !empty($weight_based_cost_address)) {
					$shipping_price = (float) $weight_based_cost_address;
					$shipping_price_cod = (float) $weight_based_cost_address + (float) $shipping_price_extra;
				}

				if ($delivery == 'office' && !empty($weight_based_cost_office)) {
					$shipping_price = (float) $weight_based_cost_office;
					$shipping_price_cod = (float) $weight_based_cost_office + (float) $shipping_price_extra;
				}

				//
				// update shipping method info
				$this->session->data['shipping_method'] = $this->session->data['shipping_methods']['pharmacy_econt']['quote']['pharmacy_econt'];

				$this->session->data['shipping_method']['cost'] = number_format((float) $shipping_price, 2, '.', '');
				$this->session->data['shipping_method']['text'] = number_format((float) $shipping_price, 2, '.', '') . $this->session->data['econt_delivery']['customer_info']['shipping_price_currency_sign'];

				$this->session->data['shipping_method']['cost_cod'] = number_format((float) $shipping_price_cod, 2, '.', '');
				$this->session->data['shipping_method']['text_cod'] = number_format((float) $shipping_price_cod, 2, '.', '') . $this->session->data['econt_delivery']['customer_info']['shipping_price_currency_sign'];

				// output
				$data['shipping_price'] = $shipping_price;
				$data['shipping_price_cod'] = $shipping_price_cod;
				$data['shipping_price_extra'] = $shipping_price_extra;

				$data['econt_price'] = (float) $this->session->data['econt_delivery']['customer_info']['shipping_price'];
				$data['econt_price_cod'] = (float) $this->session->data['econt_delivery']['customer_info']['shipping_price_cod'];

			}


			//
			// update econt shipping method, if payment method cod
			if (isset($this->session->data['payment_method']['code'])) {

				$code = $this->session->data['payment_method']['code'];
				$payment_methods = array('cod.cod', 'pharmacy_econt.pharmacy_econt');

				if (in_array($code, $payment_methods)) {

					//
					// override shipping price (weight based)
					if ($delivery == 'address' && !empty($weight_based_cost_address)) {
						$shipping_price = (float) $weight_based_cost_address;
						$shipping_price_cod = (float) $weight_based_cost_address + (float) $shipping_price_extra;
					}

					if ($delivery == 'office' && !empty($weight_based_cost_office)) {
						$shipping_price = (float) $weight_based_cost_office;
						$shipping_price_cod = (float) $weight_based_cost_office + (float) $shipping_price_extra;
					}

					// update shipping cost
					$this->session->data['shipping_method']['cost'] = number_format((float) $shipping_price_cod, 2, '.', '');
					$this->session->data['shipping_method']['text'] = number_format((float) $shipping_price_cod, 2, '.', '') . $this->session->data['econt_delivery']['customer_info']['shipping_price_currency_sign'];

				}

			}


		}

		return (array)$data;

	}



}
