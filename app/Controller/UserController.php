<?php namespace Controller;

class UserController {
	
	public $pdo = null;
	
	public function __construct( $pdo ) {
		$this->pdo = $pdo;
		
	}
	
	public function users( $strUserName = null ) {		
		$selectUserStatement = $this->pdo->select()->from('users');
		$stmt = $selectUserStatement->execute();
		$data = $stmt->fetch();
		
		var_dump($data);
		die("Class: ". __CLASS__);
		
	} 
} 
