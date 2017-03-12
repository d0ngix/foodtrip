<?php namespace Utilities;

class UserUtil
{
	public $db = null;
	
	public function __construct( $db = null , $jwt = null) {
		
		$this->db = $db;
		$this->jwt = $jwt;
			
	}	

/**
 * @method checkUser - check if uuid for valid user
 * @return int userId 
 */	
	public function checkUser ($strUuid) {
		
		$isUserExist = $this->db->select(array('id'))->from('users')->where('uuid','=',$strUuid);
		$isUserExist = $isUserExist->execute()->fetch();

		if (!$isUserExist) return false;
		
		return $isUserExist['id'];
	
	}
	
/**
 * @method checkVendor - check if uuid for valid vendor
 * @return int vendorId
 */
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
		
		$secret = $_ENV['JWT_SECRET'];
		$token = $this->jwt->encode($payload, $secret, "HS256");

		return $token;
		
	}
	
/**
 * @method generatePassword - generate new password
 * @return string
 */	
	public function generatePassword () {
			
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		
		return implode($pass); //turn the array into a string
	
	}

/**
 * @method getUserDetails - return all user details + user stored addresses
 * @return array
 */
	public function getUserDetails ($strUserUuid) {
		$selectUserStatement = $this->db->select()->from('users')->where('uuid','=',$strUserUuid);
		$stmt = $selectUserStatement->execute(false);
		$dataUser = $stmt->fetch();
		
		if (!empty($dataUser['photo']))
			$dataUser['photo'] = json_decode($dataUser['photo'], true);
		
		if (!empty($dataUser['password']))
			unset($dataUser['password']);
		
		//get user addresses
		$selectStatement = $this->db->select()->from('addresses')->where('user_id','=',$dataUser['id']);
		$selectStatement = $selectStatement->execute(false);
		$dataAddress = $selectStatement->fetchAll();
		
		if (!empty($dataUser))
			return ['user' => $dataUser,'addresses' => $dataAddress];

		return false;
	}
}