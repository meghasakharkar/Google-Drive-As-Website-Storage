<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Drive_config_model extends CI_Model{
	
	public $hid;
	public $authorization_code;
	public $access_token;
	public $refresh_token;

	private $table = 'DriveConfig';
	
	public function __construct(){
		parent::__construct();
		$this->load->database();
	}

	/*
	*	Store the authorization code 
	*/
	public function store_authorization_code(){
		$query1 = "delete from DriveConfig";
		$query2 = "insert into DriveConfig values(default,'".$this->authorization_code."', default, default)";
		$this->db->query($query1);
		$this->db->query($query2);
		return true;
	}

	/*
	* 	Store access token
	*/
	public function store_authentication_code(){
		$query = "update DriveConfig set access_token='".$this->access_token."', refresh_token='".$this->refresh_token."'";
		$this->db->query($query);
		return true;
	}

	/*
	* 	Get DriveConfig object
	*/
	public function get_drive_config_object(){
		$query = "select * from DriveConfig";
		$records = $this->db->query($query);
		$o=new Drive_config_model;
		foreach($records->result() as $row){
				$o->hid=$row->hid;
				$o->authorization_code=$row->authorization_code;
				$o->access_token=$row->access_token;
				$o->refresh_token=$row->refresh_token;
				break;
			}
		return $o;
	}

	

}