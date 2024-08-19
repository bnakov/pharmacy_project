<?php

namespace Opencart\Catalog\Controller\Extension\PharmacySpecialProducts\Module;

use \Opencart\System\Engine\Controller;

class PharmacySpecialProducts extends Controller {

	public function index(): string {

		//
		// module language
		$this->load->language('extension/pharmacy_special_products/module/pharmacy_special_products');

		//
		// special products module data
		$data['module_data'] = !empty($this->config->get('module_pharmacy_special_products')) ? $this->config->get('module_pharmacy_special_products') : array();
		$data['status'] = $this->config->get('module_pharmacy_special_products_status');
		$data['special']['products_per_page'] = (isset($data['module_data']['products']) ? $data['module_data']['products'] : 8);
		$data['special']['axis'] = (isset($data['module_data']['axis']) ? $data['module_data']['axis'] : 'vertical');
		$data['special']['width'] = (isset($data['module_data']['width']) ? $data['module_data']['width'] : 200);
		$data['special']['height'] = (isset($data['module_data']['height']) ? $data['module_data']['height'] : 200);

		//
		// get the ids of all special products
		$this->load->model('extension/pharmacy_special_products/module/pharmacy_special_products');
		$special_ids = $this->model_extension_pharmacy_special_products_module_pharmacy_special_products->get_special_products($data['special']['products_per_page']);

		//
		// get all special products
		$special_products = array(); // helper
		$data['special']['products'] = []; // html output
		$this->load->model('extension/pharmacy_theme/catalog/product');
		$this->load->model('tool/image');

		if (!empty($special_ids)) {

			//
			// load the special products
			foreach ($special_ids as $item) {
				$product_info = $this->model_extension_pharmacy_theme_catalog_product->getProduct($item['product_id']);
				if ($product_info) {
					$special_products[] = $product_info;
				}
			}

			//
			// build html data
			foreach ($special_products as $product) {
				if ($product['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $data['special']['width'], $data['special']['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $data['special']['width'], $data['special']['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				// echo "<pre>";
				// var_dump($product);
				// echo "</pre>";
				// die();

				$reviews_count = (int)$product['reviews'];
				$reviews = $reviews_count === 1 
					? sprintf($this->language->get('text_review'), $reviews_count)
					: sprintf($this->language->get('text_reviews'), $reviews_count);

				$product_data = [
					'product_id'   => $product['product_id'],
					'quantity'     => isset($product['quantity']) ? (int) $product['quantity'] : 0, // my code
					'thumb'        => $image,
					'name'         => $product['name'],
					'description'  => oc_substr(trim(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
					'price'        => $price,
					'special'      => $special,
					'tax'          => $tax,
					'reviews'      => $reviews,
					'manufacturer' => $product['manufacturer'],
					'minimum'      => $product['minimum'] > 0 ? $product['minimum'] : 1,
					'rating'       => (int)$product['rating'],
					'href'         => $this->url->link('pharmacy/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
				];

				//
				// thumb twig data
				$data["special"]["products"][] = $this->load->controller("pharmacy/product/thumb", $product_data);
			}

		}

		//
		// link to offers page
		$url = '&sort=pd.name&order=ASC&limit=25'; // sort by name asc, limit 25
		$data["link_special_products"] = $this->url->link('pharmacy/offer', 'language=' . $this->config->get('config_language') . $url);

		//
		// html part output
		if ($data["special"]["products"]) {
			return $this->load->view('extension/pharmacy_special_products/module/pharmacy_special_products', $data);
		} else {
			return '';
		}

	}

}