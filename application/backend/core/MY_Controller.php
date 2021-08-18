<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {
	public $userInfo = array();
	public $lab_code = 1;
	public function __construct() {
        parent::__construct();
		$this->userInfo = $this->session->userdata('userInfo');
		$lab_code = $this->session->userdata('lab_code');
		if($lab_code) {
			$this->lab_code = $lab_code;
		} else {
			$this->lab_code = 1;
		}
    }

    public function _denyUser($isAjax=true) {
		if($isAjax) {
			$data = array(
				'success' => false,
				'msg'		=> 'La acci�n requiere iniciar sesi�n. Por favor Iniciar sesi�n...'
			);
			extjs_output($data);
		} else {
			redirect('login');
		}
	}
}
