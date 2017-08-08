<?php

class ControllerExtensionModuleSpecialPrices extends Controller {

  private $error = [];

  public function index() {
    $this->load->language('extension/module/special_prices');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('module_special_prices', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', TRUE));
    }

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    }
    else {
      $data['error_warning'] = '';
    }

    $data['breadcrumbs'] = [];

    $data['breadcrumbs'][] = [
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], TRUE),
    ];

    $data['breadcrumbs'][] = [
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', TRUE),
    ];

    $data['breadcrumbs'][] = [
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/module/special_prices', 'user_token=' . $this->session->data['user_token'], TRUE),
    ];

    $data['action'] = $this->url->link('extension/module/special_prices', 'user_token=' . $this->session->data['user_token'], TRUE);

    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', TRUE);

    if (isset($this->request->post['module_special_prices_status'])) {
      $data['module_special_prices_status'] = $this->request->post['module_special_prices_status'];
    }
    else {
      $data['module_special_prices_status'] = $this->config->get('module_special_prices_status');
    }

    // TODO finis edit output for module
    //    $data['header'] = $this->load->controller('common/header');
    //    $data['column_left'] = $this->load->controller('common/column_left');
    //    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/special_prices', $data));
  }

  public function specialPrices() {
    $this->load->model('customer/customer');
    $this->load->model('extension/module/special_prices');
    $this->load->model('catalog/product');

    if (isset($this->request->get['customer_id'])) {
      $data['customer_id'] = $this->request->get['customer_id'];
      $data['user_token'] = $this->request->get['user_token'];
      $customer_info = $this->model_customer_customer->getCustomer($this->request->get['customer_id']);
    }

    if (isset($customer_info)) {
      $data['customer_full_name'] = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
    }

    // TODO: prepare pagination
    if (isset($this->request->get['page'])) {
      $page = $this->request->get['page'];
    }
    else {
      $page = 1;
    }

    $data['special_products'] = $this->model_extension_module_special_prices->getProducts($this->request->get['customer_id'], ($page - 1) * 10, 10);
    foreach ($data['special_products'] as $key => $product) {
      $ean_code = $product['product_id'];
      $productByEan = $this->model_extension_module_special_prices->findProductByEan($ean_code);
      if ($productByEan) {
        $product_id = $productByEan[0]['product_id'];
        $product_description = $this->model_catalog_product->getProductDescriptions($product_id);
        $product_name = $product_description[1]['name'];
        $product_url = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token']. '&product_id='.$product_id, TRUE);
        $data['special_products'][$key]['product_link'] = '<a href="' . $product_url . '">' . $product_name . '</a>';
      }
    }

    $products_total = count($data['special_products']);
    $data['results'] = sprintf($this->language->get('text_pagination'), ($products_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($products_total - 10)) ? $products_total : ((($page - 1) * 10) + 10), $products_total, ceil($products_total / 10));

    $this->response->setOutput($this->load->view('extension/module/special_prices', $data));
  }

  public function addProduct() {
    $this->load->model('extension/module/special_prices');
    $products = [];
    $customer_id = '';

    if (isset($this->request->get['customer_id'])) {
      $customer_id = $this->request->get['customer_id'];
    }

    $content = file_get_contents('php://input');
    $lines = explode("\n", $content);
    foreach ($lines as $line) {
      $csv_row = str_getcsv($line);
      // TODO: add namespaces
      // 0 - product id
      // 1 - product price
      if (is_numeric($csv_row[0])) {
        $product = [
          'product_id' => $csv_row[0],
          'customer_id' => $customer_id,
          'uploaded_by' => $this->user->getUserName(),
        ];
        if (is_numeric($csv_row[1])) {
          $product['product_price'] = $csv_row[1];
        }
        else {
          $product['product_price'] = NULL;
        }
        array_push($products, $product);
      }
    }
    $isProductsAdded = $this->model_extension_module_special_prices->addProducts($products);
    if ($isProductsAdded) {
      $this->response->setOutput('{"status":"success"}');
    }
    else {
      $this->response->setOutput('{"status":"failed"}');
    }

  }

  public function deleteProduct() {
    $this->load->model('extension/module/special_prices');

    if (isset($this->request->get['customer_id']) && isset($this->request->get['product_id'])) {
      $customer_id = $this->request->get['customer_id'];
      $product_id = $this->request->get['product_id'];

      $isProductsDeleted = $this->model_extension_module_special_prices->deleteProduct($product_id, $customer_id);

      if ($isProductsDeleted) {
        $this->response->setOutput('{"status":"success"}');
      }
      else {
        $this->response->setOutput('{"status":"failed"}');
      }
    }
    else {
      $this->response->setOutput('{"status":"failed"}');
    }
  }

  public function editProduct() {
    $this->load->model('extension/module/special_prices');
    $response['status'] = 'failed';
    if (isset($this->request->get['customer_id']) && isset($this->request->get['product_id'])) {
      $customer_id = $this->request->get['customer_id'];
      $product_id = $this->request->get['product_id'];

      if (isset($this->request->post['data'])) {
        $data = $this->request->post['data'];
        $this->model_extension_module_special_prices->editProduct($product_id, $customer_id, $data);
        $response['status'] = 'success';
      }
    }
    $this->response->setOutput(json_encode($response));
  }

  public function deleteCustomerProducts() {
    $this->load->model('extension/module/special_prices');
    $response['status'] = 'failed';
    if (isset($this->request->get['customer_id'])) {
      $customer_id = $this->request->get['customer_id'];
      $isProductsDeleted = $this->model_extension_module_special_prices->deleteCustomerProducts($customer_id);
      if ($isProductsDeleted) {
        $response['status'] = 'success';
      }
    }
    $this->response->setOutput(json_encode($response));
  }

  public function findProductByEan($ean_code) {

  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/module/special_prices')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
}