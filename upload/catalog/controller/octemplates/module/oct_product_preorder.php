<?php

class ControllerOctemplatesModuleOctProductPreorder extends Controller {
    public function index() {
        $data = array();

        $this->load->language('octemplates/module/oct_product_preorder');

        if (isset($this->request->post['product_id'])) {
            $product_id = $this->request->post['product_id'];
        } else {
            die();
        }

        $this->load->model('catalog/product');

        $product_info  = $this->model_catalog_product->getProduct($product_id);
        $data['href']  = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
        $data['model'] = $product_info['model'];

        if ($product_info) {
            $this->load->model('tool/image');

            if ($product_info['image']) {
                $data['thumb'] = $this->model_tool_image->resize($product_info['image'], 300, 200);
            } else {
                $data['thumb'] = '';
            }

            if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
                $data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $data['price'] = false;
            }

            if ((float) $product_info['special']) {
                $data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $data['special'] = false;
            }

            $data['heading_title_product'] = $product_info['name'];
            $data['href']                  = $this->url->link('product/product', 'product_id=' . $product_info['product_id']);
            $data['model']                 = $product_info['model'];
        }

        $oct_product_preorder_data         = (array) $this->config->get('oct_product_preorder_data');
        $oct_product_preorder_text         = (array) $this->config->get('oct_product_preorder_text');
        $data['oct_product_preorder_data'] = $oct_product_preorder_data;

        $data['mask'] = ($oct_product_preorder_data['mask']) ? $oct_product_preorder_data['mask'] : '';

        $data['text_promo'] = html_entity_decode($oct_product_preorder_text[$this->session->data['language']]['promo'], ENT_QUOTES, 'UTF-8');

        $data['name']      = ($this->customer->isLogged()) ? $this->customer->getFirstName() : '';
        $data['telephone'] = ($this->customer->isLogged()) ? $this->customer->getTelephone() : '';
        $data['email']     = ($this->customer->isLogged()) ? $this->customer->getEmail() : '';
        $data['comment']   = '';

        if ($this->config->get('config_account_id')) {
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

            if ($information_info) {
                $data['text_terms'] = sprintf($this->language->get('text_oct_terms'), $this->url->link('information/information', 'information_id=' . $this->config->get('config_account_id'), 'SSL'), $information_info['title'], $information_info['title']);
            } else {
                $data['text_terms'] = '';
            }
        } else {
            $data['text_terms'] = '';
        }

        $this->response->setOutput($this->load->view('octemplates/module/oct_product_preorder', $data));
    }

    public function send() {
        $json = array();

        $this->load->language('octemplates/module/oct_product_preorder');
        $this->load->model('octemplates/module/oct_product_preorder');

        $oct_product_preorder_data = (array) $this->config->get('oct_product_preorder_data');

        if (isset($this->request->post['name'])) {
            if ((isset($oct_product_preorder_data['name']) && $oct_product_preorder_data['name'] == 2) && (utf8_strlen(trim($this->request->post['name'])) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
                $json['error']['field']['name'] = $this->language->get('error_name');
            }
        }

        if (isset($this->request->post['email'])) {
            if ((isset($oct_product_preorder_data['email']) && $oct_product_preorder_data['email'] == 2)) {
                if ((utf8_strlen(trim($this->request->post['email'])) < 1)) {
                    $json['error']['field']['email'] = $this->language->get('error_email');
                }
                if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
                    $json['error']['field']['email'] = $this->language->get('error_valid_email');
                }
            }

            if ((isset($oct_product_preorder_data['email']) && $oct_product_preorder_data['email'] == 1)) {
                if ((utf8_strlen(trim($this->request->post['email'])) > 1)) {
                    if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
                        $json['error']['field']['email'] = $this->language->get('error_valid_email');
                    }
                }
            }
        }

        if (isset($this->request->post['telephone'])) {
            if ((isset($oct_product_preorder_data['telephone']) && $oct_product_preorder_data['telephone'] == 2) && (utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
                $json['error']['field']['telephone'] = $this->language->get('error_telephone');
            }
        }

        if (isset($this->request->post['comment'])) {
            if ((isset($oct_product_preorder_data['comment']) && $oct_product_preorder_data['comment'] == 2) && (utf8_strlen($this->request->post['comment']) < 3) || (utf8_strlen($this->request->post['comment']) > 500)) {
                $json['error']['field']['comment'] = $this->language->get('error_comment');
            }
        }

        if ($this->config->get('config_account_id')) {
            if (!isset($this->request->post['terms'])) {
                $this->load->model('catalog/information');

                $information_info = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));

                $json['error']['field']['terms'] = sprintf($this->language->get('error_oct_terms'), $information_info['title']);
            }
        }

        if (!isset($json['error'])) {

            $post_data = $this->request->post;

            if (isset($post_data['name'])) {
                $data[] = array(
                    'name' => $this->language->get('enter_name'),
                    'value' => $post_data['name']
                );
            }

            if (isset($post_data['telephone'])) {
                $data[] = array(
                    'name' => $this->language->get('enter_telephone'),
                    'value' => $post_data['telephone']
                );
            }

            if (isset($post_data['comment'])) {
                $data[] = array(
                    'name' => $this->language->get('enter_comment'),
                    'value' => $post_data['comment']
                );
            }

            if (isset($post_data['pid'])) {
                $data[] = array(
                    'name' => $this->language->get('enter_referer'),
                    'value' => $post_data['pid']
                );
            }

            if (isset($post_data['email'])) {
                $data[] = array(
                    'name' => $this->language->get('enter_email'),
                    'value' => $post_data['email']
                );
            }

            $linkgood = $post_data['pid'] . "\r\n" . $post_data['mid'];

            $data_send = array(
                'info' => serialize($data)
            );

            $this->model_octemplates_module_oct_product_preorder->addRequest($data_send, $linkgood);

            $json['success'] = $this->language->get('text_success_send');

            if ($oct_product_preorder_data['notify_status']) {

                // oct_cool_email_template start
                $this->load->model('tool/image');
                $this->load->model('catalog/product');

                $data['oct_ultrastore_data'] = $oct_ultrastore_data = $this->config->get('theme_oct_ultrastore_data');

                $oct_ultrastore_contact_address = $oct_ultrastore_data['contact_address'];

                $results = $this->model_localisation_language->getLanguages();

                if(isset($results[$this->session->data['language']])) {
                    $lang_id = $results[$this->session->data['language']]['language_id'];
                }

                if (isset($oct_ultrastore_contact_address[$lang_id]) && !empty($oct_ultrastore_contact_address[$lang_id])) {
                    $data['oct_ultrastore_contact_address'] = html_entity_decode($oct_ultrastore_contact_address[$lang_id], ENT_QUOTES, 'UTF-8');
                } else {
                    $data['oct_ultrastore_contact_address'] = false;
                }

                if (isset($oct_ultrastore_data['contact_telephone']) && !empty($oct_ultrastore_data['contact_telephone'])) {
                    $data['oct_ultrastore_contact_telephone'] = array_values(array_filter(explode(PHP_EOL, $oct_ultrastore_data['contact_telephone'])));
                } else {
                    $data['oct_ultrastore_contact_telephone'] = false;
                }

                $oct_ultrastore_contact_open = $oct_ultrastore_data['contact_open'];

                if (isset($oct_ultrastore_contact_open[$lang_id]) && !empty($oct_ultrastore_contact_open[$lang_id])) {
                    $data['oct_ultrastore_contact_open'] = array_values(array_filter(explode(PHP_EOL, $oct_ultrastore_contact_open[$lang_id])));
                } else {
                    $data['oct_ultrastore_contact_open'] = false;
                }

                $oct_ultrastore_socials = $oct_ultrastore_data['socials'];

                if (isset($oct_ultrastore_socials) && !empty($oct_ultrastore_socials)) {
                    $data['oct_ultrastore_socials'] = $oct_ultrastore_socials;
                } else {
                    $data['oct_ultrastore_socials'] = false;
                }
                // oct_cool_email_template end

                $html_data['date_added'] = date('m/d/Y h:i:s a', time());
                $html_data['logo']       = $this->config->get('config_url') . 'image/' . $this->config->get('config_logo');
                $html_data['store_name'] = $this->config->get('config_name');
                $html_data['store_url']  = $this->config->get('config_url');

                $html_data['text_info']       = $this->language->get('text_info');
                $html_data['text_date_added'] = $this->language->get('text_date_added');
                $html_data['data_info']       = $data;

                $html_data['text_footer'] = $this->language->get('text_footer');

                $html = $this->load->view('octemplates/mail/oct_product_preorder_mail', $html_data);

                $mail                = new Mail();
                $mail->protocol      = $this->config->get('config_mail_protocol');
                $mail->parameter     = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port     = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender($this->config->get('config_name'));
                $mail->setSubject($this->language->get('heading_title') . " -- " . $html_data['date_added']);
                $mail->setHtml($html);

                $emails = explode(',', $oct_product_preorder_data['notify_email']);

                foreach ($emails as $email) {
                    if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
                        $mail->setTo($email);
                        $mail->send();
                    }
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}