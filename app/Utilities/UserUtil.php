<?php namespace Utilities;

class UserUtil
{
	public $db = null;
	
	public function __construct( $db = null ) {
		
		$this->db = $db;
			
	}	

	//check if uuid for valid user
	public function checkUser ($strUuid) {
		
		$isUserExist = $this->db->select(array('id'))->from('users')->where('uuid','=',$strUuid);
		$isUserExist = $isUserExist->execute()->fetch();

		if (!$isUserExist) return false;
		
		return $isUserExist['id'];
	
	}
	
	//check if uuid for valid vendor
	public function checkVendor ($strUuid) {
	
		$pdoObject = $this->db->select(array('id'))->from('vendors')->where('uuid','=',$strUuid);
		$pdoObject = $pdoObject->execute()->fetch();

		if (!$pdoObject['id'])
			return "Invalid Vendor!";
	
		return (int)$pdoObject['id'];
		
	}
	
	

		
}