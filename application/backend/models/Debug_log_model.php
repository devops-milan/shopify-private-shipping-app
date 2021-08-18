<?php  if(!defined('BASEPATH')) exit('No direct script access allowed');
class Debug_log_model extends MY_Model
{
	public function __construct()
	{
        $this->table = 'debug_log';
        $this->primary_key = 'id';
        //$this->soft_deletes = true;

		parent::__construct();
	}
}
/* End of file '/User_model.php' */
/* Location: ./application/models//User_model.php */