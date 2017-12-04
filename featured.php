<?php
class ControllerExtensionModuleFeatured extends Controller {
    public function index($setting) {
        $this->load->language('extension/module/featured');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        $data['products'] = array();

        if (!$setting['limit']) {
            $setting['limit'] = 4;
        }

        if (!empty($setting['product'])) {
            $products = array_slice($setting['product'], 0, (int)$setting['limit']);

            foreach ($products as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    if ($product_info['image']) {
                        $image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
                    }

                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $price = false;
                    }

                    if ((float)$product_info['special']) {
                        $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $special = false;
                    }

                    if ($this->config->get('config_tax')) {
                        $tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
                    } else {
                        $tax = false;
                    }

                    if ($this->config->get('config_review_status')) {
                        $rating = $product_info['rating'];
                    } else {
                        $rating = false;
                    }

                    if($this->model_catalog_product->getProductOptions($product_info['product_id']))
                    {
                        $options = array();
                        $product_option_value_data = array();
                        foreach ($this->model_catalog_product->getProductOptions($product_info['product_id']) as $option)
                        {
                            foreach ($option['product_option_value'] as $option_value) {
                                $product_option_value_data[] = array(
                                    'product_option_value_id' => $option_value['product_option_value_id'],
                                    'option_value_id'         => $option_value['option_value_id'],
                                    'name'                    => $option_value['name'],
                                    //'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
                                    'price'                   => $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                                    'price_prefix'            => $option_value['price_prefix']
                                );
                                //print_r($option['type']);

                            }
                            if ($option['type'] == 'select') {
                                //print_r($option['type']);
                                $options = array(
                                    'product_option_id' => $option['product_option_id'],
                                    'product_option_value' => $product_option_value_data,
                                    'option_id' => $option['option_id'],
                                    'name' => $option['name'],
                                    'type' => $option['type'],
                                    'value' => $option['value'],
                                    'required' => $option['required']
                                );

                                //print_r($options);
                                //exit();



                            }

                        }
                    }
                    else
                    {
                        $options = false;
                    }
                    $data['products'][] = array(
                        'product_id'  => $product_info['product_id'],
                        'thumb'       => $image,
                        'name'        => $product_info['name'],
                        'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                        'price'       => $price,
                        'special'     => $special,
                        'tax'         => $tax,
                        'rating'      => $rating,
                        'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                        'options'     => $options
                    );
                }
            }
        }

        if ($data['products']) {
            return $this->load->view('extension/module/featured', $data);
        }
    }
}