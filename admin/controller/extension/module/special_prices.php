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

    if (isset($this->request->get['customer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      $data['customer_id'] = $this->request->get['customer_id'];
      $data['user_token'] = $this->request->get['user_token'];
      $customer_info = $this->model_customer_customer->getCustomer($this->request->get['customer_id']);
    }


    if (isset($customer_info)) {
      $data['customer_full_name'] = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
    }

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
      if (is_numeric($csv_row[0]) && is_numeric($csv_row[1])) {
        $product = [
          'product_id' => $csv_row[0],
          'product_price' => $csv_row[1],
          'customer_id' => $customer_id,
          'uploaded_by' => $this->user->getUserName(),
        ];
        array_push($products, $product);
      }
    }
    $isProductsAdded = $this->model_extension_module_special_prices->addProducts($products);
    if ($isProductsAdded) {
      $this->response->setOutput('{"status":"success"}');
    } else {
      $this->response->setOutput('{"status":"failed"}');
    }

  }

  protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/module/special_prices')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
}