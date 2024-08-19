<?php 

namespace Opencart\Catalog\Model\Extension\PharmacySpecialProducts\Module;

use \Opencart\System\Engine\Model;

class PharmacySpecialProducts extends Model {

    // return random special product ids
    public function get_special_products(int $limit = 0): array {

        $sql = "SELECT `product_id` FROM `" . DB_PREFIX . "product_special` GROUP BY (product_id) ORDER BY RAND()";
        $sql .= (($limit > 0) ? " LIMIT " . (int)$limit : '');

        $query = $this->db->query($sql);
        return (!empty($query->rows) ? $query->rows : []);

    }

}