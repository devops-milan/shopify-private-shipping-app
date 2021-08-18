<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shipping extends MX_Controller {

    public function __construct()
    {
        parent::__construct();
        //Do your magic here
        $this->load->model('Store_model');
        $this->load->model('Shipping_setting_model');
        $this->load->model('Debug_log_model');
        $this->load->library('form_validation');
    }

	public function index() {

	}

    public function rates($store_id='') {
        $time_log = date('Y-m-d H:i:s');
        $input = file_get_contents('php://input');
        $log = array(
            'store_id'  => $store_id,
            'key'   => 'shipping-rates-input',
            'log'   => $input,
            'created_at'    => $time_log
        );
        $this->Debug_log_model->insert($log);

        // parse the request
        $rates = json_decode($input, true);

        // log the array format for easier interpreting
        $log['key'] = 'shipping-rates-debug';
        $log['log'] = print_r($rates, true);
        $this->Debug_log_model->insert($log);

        $rates = $this->_iconsignit_shipping($rates, $store_id);
        // build the array of line items using the prior values
        $output = array('rates' => $rates);

        // encode into a json response
        $json_output = json_encode($output);

        $log['key'] = 'shipping-rates-output';
        $log['log'] = print_r($output, true);
        $this->Debug_log_model->insert($log);

        // send it back to shopify
        print $json_output;
    }

    private function _iconsignit_shipping($request, $store_id='') {
        if(!empty($store_id)) {
            $store_info = $this->Store_model->where(array('id'=>$store_id))->get();
        } else {
            $store_info = $this->Store_model->get();
        }
        $setting_info = $this->Shipping_setting_model->where(array('store_id'=>$store_info['id']))->get();
        $data1 = array(
            'API_KEY' => $store_info['api_key'],
            'API_SECRET'=> $store_info['api_password'],
            'SHOP_DOMAIN' => $store_info['domain'],
            'ACCESS_TOKEN' => $store_info['api_password']
        );

        $this->load->library('Shopify' , $data1);

        $item = array();
        $count = 0;
        foreach ($request['rate']['items'] as $values) {
            $item[$count]['item_qty'] = $values['quantity'];
            $item[$count]['item_length'] = 75;
            $item[$count]['item_width'] = 75;
            $item[$count]['item_height'] = 44;
            $item[$count]['item_weight'] = 22.5;
            $item[$count]['item_palletised'] = 0;
            $product_info = $this->shopify->call(array('METHOD' => 'GET', 'URL' =>'/admin/products/'.$values['product_id'].'.json?'),TRUE);
            if($product_info->product) {
                foreach($product_info->product->variants as $variant) {
                    if($variant->id == $values['variant_id']) {
                        $item[$count]['item_weight'] = $variant->weight > 0 ? $variant->weight : 1;
                        break;
                    }
                }
            }
            $count++;
        }
        $postcode = $request['rate']['destination']['postal_code'];
        $city = $request['rate']['destination']['city'];
        //$data = array('ApiUrl' => 'https://protec-consulting.myshopify.com', 'ApiToken' => 'a6kP5vwI57FQ0fWQk3NZmww5Bc7O3Plo', 'DeliveryTown' => $city, 'DeliveryPostcode' => $postcode, 'Items' => $item);
        $data = array('ApiUrl' => $setting_info['ApiUrl'], 'ApiToken' => $setting_info['ApiToken'], 'DeliveryTown' => $city, 'DeliveryPostcode' => $postcode, 'Items' => $item);
        $response = $this->_curl_post($setting_info['ConnectIconsignUrl'].'/api/getconsignrate', json_encode($data));

        $time_log = date('Y-m-d H:i:s');
        $data['post_url'] = $setting_info['ConnectIconsignUrl'].'/api/getconsignrate';
        $log = array(
            'store_id'  => $store_id,
            'key'   => 'shipping-service-request',
            'log'   => print_r($data, true),
            'created_at'    => $time_log
        );
        $this->Debug_log_model->insert($log);

        $log['key'] = 'shipping-service-response';
        $log['log'] = print_r($response, true);
        $this->Debug_log_model->insert($log);

        $reg_min_date = date('Y-m-d H:i:s O', strtotime('+3 days'));
        $reg_max_date = date('Y-m-d H:i:s O', strtotime('+7 days'));

        $resp = json_decode($response, true);

        $rates = array();
        if(isset($resp['result']) && !empty($resp['result'])){
            foreach ($resp['result'] as $key => $res) {
                $rate = array(
                    'service_name' => $res['carrier_nm'] . "-(" . $res['service_nm'] . ")",
                    'service_code' => 'iconsignit:'.$res['QuoteRateID'],
                    'total_price' => $res['total_charge'] * 100,
                    'currency' => 'AUD',
                    'min_delivery_date' => $reg_min_date,
                    'max_delivery_date' => $reg_max_date
                );
                $rates[] = $rate;
            }
        }
        return $rates;
    }

    private function _curl_post($url, $post_data) {
        $ch=curl_init();
        // user credencial
        //curl_setopt($ch, CURLOPT_USERPWD, "username:passwd");
        //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        // post_data
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        $body = null;
        // error
        if (!$response) {
            $body = curl_error($ch);
            // HostNotFound, No route to Host, etc  Network related error
        } else {
            $body = $response;
        }

        curl_close($ch);

        return $body;
    }
}
