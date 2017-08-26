<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

	/*************************************************
			Constructor 
	**************************************************/
	public function __construct(){
		parent::__construct();
		/********* helper **********/
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->library('form_validation');
		/********* Library **********/
		$this->load->library('session');
		$this->load->library('drivehandler');
		/********* model **********/
		$this->load->model('drive_config_model');
	}

	

	/******************************************
			Google drive setup related
	*******************************************/

	public function authorize(){
		$obj = new DriveHandler();
		$service = $obj->generate_authorization_token();
	}

	public function oauth2callback(){
		$code = Util::checkNull($this->input->get('code', TRUE));
		if($code !=""){
			$obj = new Drive_config_model();
			$obj->authorization_code = $code;
			$obj->store_authorization_code();
			redirect(base_url()."admin/authenticate");
		}
	}

	public function authenticate(){
		$obj = new DriveHandler();
		$service = $obj->generate_access_token();
		echo "Drive setup successful";
	}

}

/* End of file admin.php */
/* Location: ./application/controllers/admin.php */