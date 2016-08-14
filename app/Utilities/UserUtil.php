<?php namespace Utilities;

class UserUtil
{
	public $db = null;
	
	public function __construct( $db = null, $jwt ) {
		
		$this->db = $db;
		$this->jwt = $jwt;
			
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
	
	//Generate JWT Token
	public function tokenized($data = array()) {

		$now = new \DateTime();
		$future = new \DateTime("now +7 days");
		$server = $_SERVER;
		
		$payload = [
			"iat" => $now->getTimeStamp(),
			"exp" => $future->getTimeStamp(),
			"user" => $data
			//"sub" => $server["PHP_AUTH_USER"],
		];
		
		$secret = "supersecretkeyyoushouldnotcommittogithub";
		$token = $this->jwt->encode($payload, $secret, "HS256");

		return $token;
		
	}
	
	

		
}