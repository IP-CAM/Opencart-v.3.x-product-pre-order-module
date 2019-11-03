<?php

class ControllerExtensionModuleOctProductPreorder extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/oct_product_preorder');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/lang/summernote-' . $this->language->get('lang') . '.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');
        $this->document->addStyle('view/javascript/summernote/summernote.css');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('oct_product_preorder', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['user_token']    = $this->session->data['user_token'];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['notify_email'])) {
            $data['error_notify_email'] = $this->error['notify_email'];
        } else {
            $data['error_notify_email'] = '';
        }

        $data['error_call_button'] = (isset($this->error['call_button'])) ? $this->error['call_button'] : '';
        $data['error_promo']       = (isset($this->error['promo'])) ? $this->error['promo'] : '';

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/oct_product_preorder', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/oct_product_preorder', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['oct_product_preorder_data'])) {
            $data['oct_product_preorder_data'] = $this->request->post['oct_product_preorder_data'];
        } else {
            $data['oct_product_preorder_data'] = $this->config->get('oct_product_preorder_data');
        }

        if (isset($this->request->post['oct_product_preorder_text'])) {
            $data['oct_product_preorder_text'] = $this->request->post['oct_product_preorder_text'];
        } else {
            $data['oct_product_preorder_text'] = $this->config->get('oct_product_preorder_text');
        }

        $this->load->model('localisation/language');
        $this->load->model('localisation/stock_status');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        $data['stock_statuses'] = array();

        foreach ($this->model_localisation_stock_status->getStockStatuses() as $stock_status) {
            $data['stock_statuses'][] = array(
                'stock_status_id' => $stock_status['stock_status_id'],
                'name' => $stock_status['name']
            );
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/oct_product_preorder', $data));
    }

    public function history() {
        $data = array();
        $this->load->model('extension/module/oct_product_preorder');
        $this->language->load('extension/module/oct_product_preorder');

        $page          = (isset($this->request->get['page'])) ? $this->request->get['page'] : 1;
        $data['user_token'] = $this->session->data['user_token'];

        $data['histories'] = array();

        $filter_data = array(
            'start' => ($page - 1) * 20,
            'limit' => 20,
            'sort' => 'r.date_added',
            'order' => 'DESC'
        );

        $results = $this->model_extension_module_oct_product_preorder->getCallArray($filter_data);

        foreach ($results as $result) {
            $info = array();

            $fields = unserialize($result['info']);

            foreach ($fields as $field) {
                $info[] = array(
                    'name' => $field['name'],
                    'value' => $field['value']
                );
            }

            $data['histories'][] = array(
                'request_id' => $result['request_id'],
                'info' => $info,
                'date_added' => $result['date_added'],
                'note' => $result['note']
            );
        }

        $history_total = $this->model_extension_module_oct_product_preorder->getTotalCallArray();

        $pagination        = new Pagination();
        $pagination->total = $history_total;
        $pagination->page  = $page;
        $pagination->limit = 20;
        $pagination->url   = $this->url->link('extension/module/oct_product_preorder/history', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 20) + 1 : 0, ((($page - 1) * 20) > ($history_total - 20)) ? $history_total : ((($page - 1) * 20) + 20), $history_total, ceil($history_total / 20));

        $this->response->setOutput($this->load->view('extension/module/oct_product_preorder_history', $data));
    }

    public function update_note() {
        $json = array();
        $this->load->model('extension/module/oct_product_preorder');

        $info = $this->model_extension_module_oct_product_preorder->getCall((int) $this->request->get['request_id']);

        if ($info) {
            $this->model_extension_module_oct_product_preorder->updateNote((int) $this->request->get['request_id'], $this->request->get['note']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete_selected() {
        $json = array();
        $this->load->model('extension/module/oct_product_preorder');

        $info = $this->model_extension_module_oct_product_preorder->getCall((int) $this->request->get['delete']);

        if ($info) {
            $this->model_extension_module_oct_product_preorder->deleteCall((int) $this->request->get['delete']);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete_all() {
        $json = array();
        $this->load->model('extension/module/oct_product_preorder');

        $this->model_extension_module_oct_product_preorder->deleteAllCallArray();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete_all_selected() {
        $json = array();
        $this->load->model('extension/module/oct_product_preorder');

        if (isset($this->request->request['selected'])) {
            foreach ($this->request->request['selected'] as $request_id) {
                $info = $this->model_extension_module_oct_product_preorder->getCall((int) $request_id);

                if ($info) {
                    $this->model_extension_module_oct_product_preorder->deleteCall((int) $request_id);
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function install() {
        $this->load->language('extension/module/oct_product_preorder');
        $this->load->model('extension/module/oct_product_preorder');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');
        $this->load->model('user/user_group');
        $this->load->model('localisation/language');

        $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'extension/module/oct_product_preorder');
        $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'extension/module/oct_product_preorder');

        $this->model_extension_module_oct_product_preorder->makeDB();

        $languages = $this->model_localisation_language->getLanguages();

        foreach ($languages as $language) {
            $default_text_data[$language['code']] = array(
                'call_button' => $this->language->get('default_call_button'),
                'promo' => $this->language->get('default_promo')
            );
        }

        $this->model_setting_setting->editSetting('oct_product_preorder', array(
            'oct_product_preorder_text' => $default_text_data,
            'oct_product_preorder_data' => array(
                'status' => '1',
                'notify_status' => '1',
                'notify_email' => $this->config->get('config_email'),
                'name' => '2',
                'telephone' => '2',
                'comment' => '2',
                'email' => '2',
                'mask' => '(999) 999-99-99',
                'stock_statuses' => array()
            )
        ));

        if (!in_array('oct_product_preorder', $this->model_setting_extension->getInstalled('module'))) {
            $this->model_setting_extension->install('module', 'oct_product_preorder');
        }

        $this->session->data['success'] = $this->language->get('text_success_install');
    }

    public function uninstall() {
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');
        $this->load->model('extension/module/oct_product_preorder');

        $this->model_extension_module_oct_product_preorder->deleteDB();
        $this->model_setting_extension->uninstall('module', 'oct_product_preorder');
        $this->model_setting_setting->deleteSetting('oct_product_preorder_data');
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/oct_product_preorder')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->request->post['oct_product_preorder_data'] as $key => $field) {
            if (empty($field) && $key == "notify_email") {
                $this->error['notify_email'] = $this->language->get('error_notify_email');
            }
        }

        foreach ($this->request->post['oct_product_preorder_text'] as $language_code => $value) {
            if ((utf8_strlen($value['call_button']) < 1) || (utf8_strlen($value['call_button']) > 255)) {
                $this->error['call_button'][$language_code] = $this->language->get('error_field');
            }

            if ((utf8_strlen($value['promo']) < 1) || (utf8_strlen($value['promo']) > 5000)) {
                $this->error['promo'][$language_code] = $this->language->get('error_field');
            }
        }

        return !$this->error;
    }
}