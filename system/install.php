<?php
$this->db->query( "ALTER TABLE " . DB_PREFIX . "order_product MODIFY quantity DECIMAL( 10,2 )" );
$this->db->query( "ALTER TABLE " . DB_PREFIX . "cart MODIFY quantity DECIMAL( 10,2 )" );
