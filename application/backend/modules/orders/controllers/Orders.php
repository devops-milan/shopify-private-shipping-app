<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Orders extends MX_Controller {

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

    public function create($store_id='') {
        $time_log = date('Y-m-d H:i:s');
        $input = file_get_contents('php://input');
        $log = array(
            'store_id'  => $store_id,
            'key'   => 'order-create-input',
            'log'   => $input,
            'created_at'    => $time_log
        );
        $this->Debug_log_model->insert($log);

        // parse the request
        $order = json_decode($input, true);
        $log['key'] = 'order-create-debug';
        $log['log'] = print_r($order, true);
        $this->Debug_log_model->insert($log);

        //$this->_iconsignit_shipping($order, $store_id);
    }

    public function paid($store_id='') {
        $time_log = date('Y-m-d H:i:s');
        $input = file_get_contents('php://input');
        $log = array(
            'store_id'  => $store_id,
            'key'   => 'order-paid-input',
            'log'   => $input,
            'created_at'    => $time_log
        );
        $this->Debug_log_model->insert($log);

        // parse the request
        $order = json_decode($input, true);
        $log['key'] = 'order-paid-debug';
        $log['log'] = print_r($order, true);
        $this->Debug_log_model->insert($log);

        $this->_iconsignit_shipping($order, $store_id);
    }

    private function _iconsignit_shipping($order, $store_id='') {
        $time_log = date('Y-m-d H:i:s');
        $log = array(
            'store_id'  => $store_id,
            'key'   => '',
            'log'   => '',
            'created_at'    => $time_log
        );

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

        if(empty($order['shipping_lines']) || strpos($order['shipping_lines'][0]['code'], 'iconsignit') === false) {
            $log['key'] = 'order-paid-return1';
            $this->Debug_log_model->insert($log);
            return;
        }
        $quoteId_patterns = explode(':', $order['shipping_lines'][0]['code']);
        if(count($quoteId_patterns) < 2) {
            $log['key'] = 'order-paid-return2';
            $this->Debug_log_model->insert($log);
            return;
        }
        $post_data = [];
        $post_data['ApiUrl'] = $setting_info['ApiUrl'];     // 'https://protec-consulting.myshopify.com';
        $post_data['ApiToken'] = $setting_info['ApiToken']; // 'a6kP5vwI57FQ0fWQk3NZmww5Bc7O3Plo';
        $post_data['QuoteRateID'] = $quoteId_patterns[1];
        $post_data['DeliveryTown'] = $order['shipping_address']['city'];
        $post_data['DeliveryPostcode'] = $order['shipping_address']['zip'];
        $post_data['DeliveryName'] = $order['shipping_address']['name'];
        $post_data['DeliveryAddressLine1'] = $order['shipping_address']['address1'];
        $post_data['DeliveryPhoneNumber'] = empty($order['shipping_address']['phone']) ? '1234567890' : $order['shipping_address']['phone'];
        $post_data['DeliveryContactName'] = $order['shipping_address']['name'];
        $post_data['DeliveryEmail'] = $order['customer']['email'];
        $post_data['DeliveryInstruction'] = NULL;
        $post_data['Items'] = array();
        $count = 0;
        foreach ($order['line_items'] as $value) {
            $post_data['Items'][$count] = array();

            $post_data['Items'][$count]['item_length'] = 1;
            $post_data['Items'][$count]['item_width'] = 1;
            $post_data['Items'][$count]['item_height'] = 1;
            $post_data['Items'][$count]['item_weight'] = 1;
            $product_info = $this->shopify->call(array('METHOD' => 'GET', 'URL' =>'/admin/products/'.$value['id'].'.json?'),TRUE);
            if($product_info->product) {
                foreach($product_info->product->variants as $variant) {
                    if($variant->id == $value['variant_id']) {
                        $post_data['Items'][$count]['item_weight'] = $variant->weight > 0 ? $variant->weight : 1;
                        break;
                    }
                }
            }
            $post_data['Items'][$count]['item_code'] = !empty($value['sku']) ? $value['sku'] : $value['id'].'_'.$value['variant_id'];
            $post_data['Items'][$count]['item_desc'] = $value['name'];
            $post_data['Items'][$count]['item_qty'] = $value['quantity'];
            $post_data['Items'][$count]['item_palletised'] = 0;
            $count++;
        }

        $log = array(
            'store_id'  => $store_id,
            'key'       => 'order-paid-request',
            'log'       => print_r($post_data, true),
            'created_at'    => $time_log
        );
        $this->Debug_log_model->insert($log);
        $response = $this->_curl_post($setting_info['ConnectIconsignUrl'].'/api/CreateConsignment', json_encode($post_data));
        $resp = json_decode($response, true);

        $log['key'] = 'order-paid-response';
        $log['log'] = print_r($resp, true);
        $this->Debug_log_model->insert($log);

        if(isset($resp['result']) && isset($resp['result']['ConsignCode'])){
            /*
            $newFulfillment = array(
                'fulfillment' => array(
                    'location_id'       => 905684977,
                    'tracking_number'   => $resp['result']['ConsignCode'],
                    'tracking_urls'     => array(
                        "https://shipping.xyz/track.php?num=123456789",
                        "https://anothershipper.corp/track.php?code=abc"
                    ),
                    'notify_customer'   => true
                )
            );
            if(!empty($order['location_id'])) {
                $newFulfillment['fulfillment']['location_id'] = $order['location_id'];
            }
            $log['key'] = 'order-paid-fulfillment-request';
            $log['log'] = print_r($newFulfillment, true);
            $this->Debug_log_model->insert($log);
            $result = $this->shopify->call(array('METHOD' => 'POST', 'URL' => '/admin/orders/'.$order['id'].'/fulfillments', 'DATA' => $newFulfillment), TRUE);
            */
            $newNote = array(
                'order' => array(
                    'id'    => $order['id'],
                    'note'  => 'IconsignitShipping Tracking Number: '.$resp['result']['ConsignCode']
                )
            );
            $log['key'] = 'order-paid-note-request';
            $log['log'] = print_r($newNote, true);
            $this->Debug_log_model->insert($log);
            $result = $this->shopify->call(array('METHOD' => 'PUT', 'URL' => '/admin/orders/'.$order['id'], 'DATA' => $newNote), TRUE);

            $log['key'] = 'order-paid-note-result';
            $log['log'] = print_r($result, true);
            $this->Debug_log_model->insert($log);
        }
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
