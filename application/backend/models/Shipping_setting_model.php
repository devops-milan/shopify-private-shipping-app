<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Shipping_setting_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'iconsignit_settings';
        $this->primary_key = 'id';
        //$this->soft_deletes = true;

		parent::__construct();
	}
}
/* End of file '/User_model.php' */
/* Location: ./application/models//User_model.php */