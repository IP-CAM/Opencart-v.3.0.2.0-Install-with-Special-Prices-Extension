<?php

class ModelExtensionModuleSpecialPrices extends Model {

  /**
   * @param $data
   *
   * @return string
   */
  public function addProducts($products) {
    try {
      $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "special_product (
        product_id int NOT NULL,
        product_price decimal(8, 2) NOT NULL,
        customer_id int NOT NULL,
        uploaded_by text NOT NULL,
        timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
    )");
      foreach ($products as $product) {
        // TODO: add validation for duplicates
        $this->db->query("INSERT INTO " . DB_PREFIX . "special_product SET product_id = '" . (int) $product['product_id'] .
          "', product_price = '" . $product['product_price'] .
          "', customer_id = '" . (int) $product['customer_id'] .
          "', uploaded_by = '" . $product['uploaded_by'] .
          "', timestamp = NOW()");
      }
    } catch (Exception $e) {
      // TODO: catch exeption
      // echo 'Caught exception: ',  $e->getMessage(), "\n";
      return FALSE;
    }

    return TRUE;
  }

  public function getProducts($customer_id, $start = 0, $limit = 10) {
    if ($start < 0) {
      $start = 0;
    }
    if ($limit < 1) {
      $limit = 10;
    }

    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "special_product WHERE customer_id = '" . (int) $customer_id . "' ORDER BY timestamp DESC LIMIT " . (int) $start . "," . (int) $limit);

    return $query->rows;
  }

  public function editProduct($product_id, $customer_id, $data) {
    $this->db->query("UPDATE " . DB_PREFIX . "special_product SET product_id = '" . $data['product_id'] . "', product_price = '" . $data['product_price'] . "' WHERE product_id = '" . (int) $product_id . "' AND customer_id = '" . (int) $customer_id . "'");
  }

  public function deleteProduct($product_id, $customer_id) {
    try {
      $this->db->query("DELETE FROM `" . DB_PREFIX . "special_product` WHERE product_id = '" . $product_id . "' AND customer_id = '" . $customer_id . "'");
    } catch (Exception $e) {
      // TODO: catch exeption
      // echo 'Caught exception: ',  $e->getMessage(), "\n";
      return FALSE;
    }
    return TRUE;
  }

}
