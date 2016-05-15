<?php namespace Utilities;

class UserUtil
{
	public $db = null;
	
	public function __construct( $db = null ) {
		
		$this->db = $db;
	
	}	

	//check if uuid for valid user
	public static function checkUser ($db, $strUuid) {

		$isUserExist = $db->select(array('count(id) "count"'))->from('users')->where('uuid','=',$strUuid);
		$isUserExist = $isUserExist->execute()->fetch();

		if (!$isUserExist['count'])
			return false;
		
		return true;
	
	}
	

		
}