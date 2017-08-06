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
        PRIMARY KEY(product_id),
        product_price decimal(8, 2) NOT NULL,
        customer_id int NOT NULL,
        uploaded_by text NOT NULL,
        timestamp int NOT NULL
    )");
      foreach ($products as $product) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "special_product SET product_id = '" . (int)$product['product_id'] .
          "', product_price = '" . $product['product_price'] .
          "', customer_id = '" . (int)$product['customer_id'] .
          "', uploaded_by = '" . $product['uploaded_by'] .
          "', timestamp = NOW()");
      }
    } catch (Exception $e) {
    // TODO: catch exeption
    // echo 'Caught exception: ',  $e->getMessage(), "\n";
      return false;
    }

		return true;
	}

}
