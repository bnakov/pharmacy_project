<?php

namespace Opencart\Admin\Model\Extension\PharmacyCheckout\Shipping;

use Opencart\System\Engine\Model;

class PharmacyEcont extends Model {

    public function install() {

        $this->db->query(sprintf("
            CREATE TABLE IF NOT EXISTS `%s`.`%secont_delivery` (
                `id_order` INT(11) NOT NULL DEFAULT '0',
                `customer_info` MEDIUMTEXT NULL,
                `econt_order` MEDIUMTEXT NULL,
                `items` MEDIUMTEXT NULL,
                `shipment_number` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
                PRIMARY KEY (`id_order`)
            )
            COLLATE = 'utf8mb4_general_ci'
            ENGINE = InnoDB
            ",
            DB_DATABASE,
            DB_PREFIX
        ));

    }

}