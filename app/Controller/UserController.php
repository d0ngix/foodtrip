<?php namespace Controller;

class UserController {

	public $pdo = null;

	public function __construct( $pdo ) {
		$this->pdo = $pdo;

	}

	public function users( $id = null ) {

		$selectUserStatement = $this->pdo->select()->from('users')->where('id','=',$id);
		$stmt = $selectUserStatement->execute();
		$data = $stmt->fetch();

		return $data;
		//die("Class: ". __CLASS__);

	}

	public function add( $data ) {
		var_dump($data);
		if (empty($data)) return false;

		var_dump(array_keys($data));

		foreach ($data as $key => $value) {
				
		}

		// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
		// 		$insertStatement = $pdo->insert(array('id', 'usr', 'pwd'))
		// 		->into('users')
		// 		->values(array(1234, 'your_username', 'your_password'));

	}
}
