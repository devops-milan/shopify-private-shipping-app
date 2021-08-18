<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends MX_Controller {

    public function __construct()
    {
        parent::__construct();
        //Do your magic here
        $this->load->model('Store_model');
        $this->load->model('Shipping_setting_model');
        $this->load->library('form_validation');
    }

	public function index() {
        $this->load->view('login');
	}

	public function save() {
        $data = array();
        $domain = $this->input->post('domain', '', '');
        $api_key = $this->input->post('api_key', '', '');
        $api_password = $this->input->post('api_password', '', '');
        $api_secret = $this->input->post('api_secret', '', '');

        if(empty($domain) || empty($api_key) || empty($api_password) || empty($api_secret)) {
            $bIsCorrect = false;
            $data['error'] = 'Please enter correct value.';
        } else {
            $data1 = array(
                'API_KEY' => $api_key,
                'API_SECRET'=>$api_password,
                'SHOP_DOMAIN' => $domain,
                'ACCESS_TOKEN' => $api_password
            );

            $this->load->library('Shopify' , $data1);

            $shippingModules = $this->shopify->call(array('METHOD' => 'GET', 'URL' =>'/admin/carrier_services'),TRUE);
            if($shippingModules && isset($shippingModules->carrier_services)) {
                $bIsCorrect = true;
                $new_store_info = array(
                    'domain'        => $domain,
                    'api_key'       => $api_key,
                    'api_password'  => $api_password,
                    'api_secret'    => $api_secret
                );
                $old_store_info = $this->Store_model->where(array('domain'=>$domain))->get();
                if(!empty($old_store_info)) {
                    $this->Store_model->where(array('domain'=>$domain))->update($new_store_info);
                    $new_store_info['id'] = $old_store_info['id'];
                } else {
                    $new_store_info['id'] = $this->Store_model->insert($new_store_info);
                }
                $this->session->set_userdata('store_info', $new_store_info);
            } else {
                $bIsCorrect = false;
                if($shippingModules->_ERROR && !empty($shippingModules->_ERROR['MESSAGE'])) {
                    $data['error'] = $shippingModules->_ERROR['MESSAGE'];
                } else {
                    $data['error'] = 'API test failed, please enter again.';
                }
            }
        }

        if($bIsCorrect) {
            redirect('shipping/setting');
        } else {
            $this->load->view('login', $data);
        }
    }
}
