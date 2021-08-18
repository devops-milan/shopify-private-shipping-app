<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setting extends MX_Controller {

    public function __construct()
    {
        parent::__construct();
        //Do your magic here
        $this->load->model('Store_model');
        $this->load->model('Shipping_setting_model');
        $this->load->library('form_validation');
    }

	public function index() {
        $store_info = $this->session->userdata('store_info');
        if(empty($store_info)) {
            redirect('auth');
            exit;
        }

        $data = array();
        $data['store_info'] = $store_info;
        $setting_info = $this->Shipping_setting_model->where(array('store_id'=>$store_info['id']))->get();
        if(empty($setting_info)) {
            $setting_info = array(
                'ApiUrl'    => '',
                'ApiToken'  => '',
                'ConnectIconsignUrl'    => ''
            );
        }
        $data['setting_info'] = $setting_info;

        $data1 = array(
            'API_KEY' => $store_info['api_key'],
            'API_SECRET'=> $store_info['api_password'],
            'SHOP_DOMAIN' => $store_info['domain'],
            'ACCESS_TOKEN' => $store_info['api_password']
        );
        $this->load->library('Shopify' , $data1);

        $data = $this->_checkAlreadyInstalled($data);

        $this->load->view('iconsignit_setting', $data);
	}

	public function save() {
        $store_info = $this->session->userdata('store_info');
        if(empty($store_info)) {
            redirect('auth');
            exit;
        }

        $data = array();
        $date['errors'] = array();
        $apiUrl = $this->input->post('ApiUrl', '', '');
        $apiToken = $this->input->post('ApiToken', '', '');
        $connectIconsignUrl = $this->input->post('ConnectIconsignUrl', '', '');

        if(empty($apiUrl) || empty($apiToken) || empty($connectIconsignUrl)) {
            $data['errors'][] = 'Please enter correct value.';
        } else {
            $new_setting_info = array(
                'store_id'      => $store_info['id'],
                'ApiUrl'        => $apiUrl,
                'ApiToken'      => $apiToken,
                'ConnectIconsignUrl'    => $connectIconsignUrl
            );
            $old_setting_info = $this->Shipping_setting_model->where(array('store_id'=>$store_info['id']))->get();
            if(!empty($old_setting_info)) {
                $this->Shipping_setting_model->where(array('store_id'=>$store_info['id']))->update($new_setting_info);
            } else {
                $this->Shipping_setting_model->insert($new_setting_info);
            }

            $data1 = array(
                'API_KEY' => $store_info['api_key'],
                'API_SECRET'=> $store_info['api_password'],
                'SHOP_DOMAIN' => $store_info['domain'],
                'ACCESS_TOKEN' => $store_info['api_password']
            );

            $this->load->library('Shopify' , $data1);

            $data = $this->_checkAlreadyInstalled($data);
            if(!$data['bInstalledCustomShipping']) {
                $data = $this->_installCarrierService($store_info['id'], $data);
                if(!isset($data['errors']) || empty($data['errors'])) {
                    $data = $this->_installWebhooksOrder($store_info['id'], $data);
                }
            }
        }

        $setting_info = $this->Shipping_setting_model->where(array('store_id'=>$store_info['id']))->get();
        if(empty($setting_info)) {
            $setting_info = array(
                'ApiUrl'    => '',
                'ApiToken'  => '',
                'ConnectIconsignUrl'    => ''
            );
        }

        //$data['errors'][] = print_r($shippingModules->carrier_services, true);
        $data['setting_info'] = $setting_info;
        $data['store_info'] = $store_info;
        $this->load->view('iconsignit_setting', $data);
    }

    public function uninstall($module_id) {
        $store_info = $this->session->userdata('store_info');
        if(empty($store_info)) {
            redirect('auth');
            exit;
        }

        $data = array();
        $data['store_info'] = $store_info;
        $setting_info = $this->Shipping_setting_model->where(array('store_id'=>$store_info['id']))->get();
        if(empty($setting_info)) {
            $setting_info = array(
                'ApiUrl'    => '',
                'ApiToken'  => '',
                'ConnectIconsignUrl'    => ''
            );
        }
        $data['setting_info'] = $setting_info;

        $data1 = array(
            'API_KEY' => $store_info['api_key'],
            'API_SECRET'=> $store_info['api_password'],
            'SHOP_DOMAIN' => $store_info['domain'],
            'ACCESS_TOKEN' => $store_info['api_password']
        );
        $this->load->library('Shopify' , $data1);

        $this->shopify->call(array('METHOD' => 'DELETE', 'URL' =>'/admin/carrier_services/'.$module_id),TRUE);
        $data = $this->_checkAlreadyInstalled($data);

        $this->load->view('iconsignit_setting', $data);
    }

    private function _checkAlreadyInstalled($data) {
        $shippingModules = $this->shopify->call(array('METHOD' => 'GET', 'URL' =>'/admin/carrier_services'),TRUE);
        $bInstalledCustomShipping = false;
        if($shippingModules && isset($shippingModules->carrier_services)) {
            foreach ($shippingModules->carrier_services as $shipService) {
                if (strpos($shipService->name, "IconsignitShipper") !== false) {
                    $bInstalledCustomShipping = true;
                    $data['old_module_id'] = $shipService->id;
                    break;
                }
            }
        } else {
            $data['errors'][] = isset($shippingModules->errors->base) ? $shippingModules->errors->base[0] : "Error: Can't install shipping module";
        }

        if($bInstalledCustomShipping) {
            $data['info'] = 'Shipping module already installed.';
        }
        $data['bInstalledCustomShipping'] = $bInstalledCustomShipping;
        return $data;
    }

    private function _installCarrierService($store_id, $data) {
        $newShippingModule = array(
            'carrier_service' => array(
                'name'              => 'IconsignitShipper',
                'callback_url'      => base_url('shipping/rates/'.$store_id),
                'service_discovery' => true,
                'format'            => "json",
            )
        );
        try{
            $result = $this->shopify->call(array('METHOD' => 'POST', 'URL' => '/admin/carrier_services', 'DATA' => $newShippingModule), TRUE);
            $errors = array();
            if($result->_ERROR && !empty($result->_ERROR['MESSAGE'])) {
                $errors[] = $result->_ERROR['MESSAGE'];
            }
            if($result && isset($result->errors)) {
                $errors[] = isset($result->errors->base) ? $result->errors->base[0] : "Error: Can't install shipping module";
            }

            if(empty($errors)) {
                $data['info'] = 'Shipping module successfully installed.';
            } else {
                foreach($errors as $err) {
                    $data['errors'][] = $err;
                }
            }
        } catch (Exception $ex) {
            $data['errors'][] = $ex->getMessage();
        }
        return $data;
    }

    private function _installWebhooksOrder($store_id, $data) {
        $newOrderHook = array(
            'webhook' => array(
                'topic'     => 'orders/create',
                'address'   => base_url('orders/create/'.$store_id),
                'format'    => "json",
            )
        );
        try{
            $result = $this->shopify->call(array('METHOD' => 'POST', 'URL' => '/admin/webhooks', 'DATA' => $newOrderHook), TRUE);
            $errors = array();
            if($result->_ERROR && !empty($result->_ERROR['MESSAGE'])) {
                $errors[] = $result->_ERROR['MESSAGE'];
            }
            if($result && isset($result->errors)) {
                $errors[] = isset($result->errors->base) ? $result->errors->base[0] : "Error: Can't install order webhook";
            }

            if(empty($errors)) {
                $data['info'] = 'Order webhook successfully installed.';
            } else {
                foreach($errors as $err) {
                    $data['errors'][] = $err;
                }
            }
        } catch (Exception $ex) {
            $data['errors'][] = $ex->getMessage();
        }

        $newOrderHook = array(
            'webhook' => array(
                'topic'     => 'orders/paid',
                'address'   => base_url('orders/paid/'.$store_id),
                'format'    => "json",
            )
        );
        try{
            $result = $this->shopify->call(array('METHOD' => 'POST', 'URL' => '/admin/webhooks', 'DATA' => $newOrderHook), TRUE);
            $errors = array();
            if($result->_ERROR && !empty($result->_ERROR['MESSAGE'])) {
                $errors[] = $result->_ERROR['MESSAGE'];
            }
            if($result && isset($result->errors)) {
                $errors[] = isset($result->errors->base) ? $result->errors->base[0] : "Error: Can't install order webhook";
            }

            if(empty($errors)) {
                $data['info'] = 'Order webhook successfully installed.';
            } else {
                foreach($errors as $err) {
                    $data['errors'][] = $err;
                }
            }
        } catch (Exception $ex) {
            $data['errors'][] = $ex->getMessage();
        }
        return $data;
    }
}
