<?php 

namespace Opencart\Catalog\Model\Extension\PharmacyProducts\Module;

use \Opencart\System\Engine\Model;

class PharmacyProducts extends Model {

    // return random special product ids
    public function get_special_products(int $limit): array {

		$special_products = [];
        $sql = "SELECT `product_id` FROM `" . DB_PREFIX . "product_special` GROUP BY (product_id) ORDER BY RAND() LIMIT {$limit}";
		$query = $this->db->query($sql);

        // echo "<pre>";
        // var_dump($query->rows);
        // echo "</pre>";
        // die();

        return (!empty($query->rows) ? $query->rows : []);

    }

}