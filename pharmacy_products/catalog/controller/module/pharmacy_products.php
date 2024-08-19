<?php

namespace Opencart\Catalog\Controller\Extension\PharmacyProducts\Module;

use \Opencart\System\Engine\Controller;

class PharmacyProducts extends Controller {

	public function index(): string {

		$this->load->language('extension/pharmacy_products/module/pharmacy_products');
		// $this->load->model('extension/pharmacy_products/module/pharmacy_products');

		// $data["featured"] = $this->model_extension_pharmacy_products_module_pharmacy_products->get_featured_products();
		// $data["topseller"] = $this->model_extension_pharmacy_products_module_pharmacy_products->get_topseller_products();
		// $data["most_searched"] = $this->model_extension_pharmacy_products_module_pharmacy_products->get_most_searched_products();

		$data['module_pharmacy_products_status'] = $this->config->get('module_pharmacy_products_status');
		$data['featured'] = !empty($this->config->get('module_pharmacy_products_featured')) ? $this->config->get('module_pharmacy_products_featured') : array();

		if (!isset($data['featured']["axis"])) {
			$data['featured']["axis"] = "horizontal";
		}

		if (!isset($data['featured']["width"])) {
			$data['featured']["width"] = 200;
		}

		if (!isset($data['featured']["height"])) {
			$data['featured']["height"] = 200;
		}

		$products = [];
		if (isset($data['featured']["products"]) && is_array($data['featured']["products"])) {
			$products = $data['featured']["products"];
		}

		$data['featured']["products"] = [];
		$this->load->model('extension/pharmacy_theme/catalog/product');
		$this->load->model('tool/image');

		$featured_products = array(); // helper
		if (!empty($products)) {

			foreach ($products as $product_id) {
				$product_info = $this->model_extension_pharmacy_theme_catalog_product->getProduct($product_id);
				if ($product_info) {
					$featured_products[] = $product_info;
				}
			}

			foreach ($featured_products as $product) {
				if ($product['image']) {
					$image = $this->model_tool_image->resize(html_entity_decode($product['image'], ENT_QUOTES, 'UTF-8'), $data['featured']["width"], $data['featured']["height"]);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $data['featured']["width"], $data['featured']["height"]);
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
				// die();

				$product_data = [
					'product_id'   => $product['product_id'],
					'quantity'     => isset($product['quantity']) ? (int) $product['quantity'] : 0, // my code
					'thumb'        => $image,
					'name'         => $product['name'],
					'description'  => oc_substr(trim(strip_tags(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('config_product_description_length')) . '..',
					'price'        => $price,
					'special'      => $special,
					'tax'          => $tax,
					'manufacturer' => $product["manufacturer"],
					'minimum'      => $product['minimum'] > 0 ? $product['minimum'] : 1,
					'rating'       => (int)$product['rating'],
					'href'         => $this->url->link('pharmacy/product', 'language=' . $this->config->get('config_language') . '&product_id=' . $product['product_id'])
				];

				// thumb twig data
				$data["featured"]["products"][] = $this->load->controller("pharmacy/product/thumb", $product_data);
			}

		}

		// echo "<pre>";
		// var_dump($data["module_pharmacy_products"]["products"]);
		// die();

		if ($data["featured"]["products"]) {
			return $this->load->view('extension/pharmacy_products/module/pharmacy_products', $data);
		} else {
			return '';
		}

	}

}
