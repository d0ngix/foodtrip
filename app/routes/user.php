<?php
//use Model\User;
//use Controller\UserController;

$app->get('/user[/{uuid}]', function ( $request, $response, $args) {
	$selectUserStatement = $this->db->select()->from('users')->where('uuid','=',$args['uuid']);
	$stmt = $selectUserStatement->execute(false);
	$data = $stmt->fetch();
	
	//unjson the photo details
	if (!empty($data)) 
		$data['photo'] = json_decode($data['photo'],true);

	$response->withJson($data, 200);

});

//Adding new user
$app->post('/user', function ( $request, $response, $args) {
	
	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson('Empty form!',500);
		return $response;
	}
	
	//search email and number if it exist
	$isExist = $this->db->select(array('email'))->from('users')->where('email','=',$data['email']);
	$isExist = $isExist->execute(false);
	
	if (!empty($isExist->fetch())) {
		$response->withJson('User already exist!',500);
		return $response;		
	}

	//check for email and password if empty
	if (empty($data['password']) || empty($data['email'])) {
		$response->withJson('Email or Password must not be empty!',500);
		return $response;
	}
	
	//password hashing 
	if (!empty($data['password'])) {
		$options = ['cost' => 12,];
		$passwordFromPost = $data['password'];
		$data['password'] = password_hash($passwordFromPost, PASSWORD_BCRYPT, $options);		
	}
	
	$data['uuid'] = uniqid();

	$arrFields = array_keys($data);
	$arrValues = array_values($data);

	// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
	$insertStatement = $this->db->insert( $arrFields )
								->into('users')
								->values($arrValues);
	$insertId = $insertStatement->execute(true);
	if (!$insertId) {
		$this->logger->addError($insertId);
		$response->withJson("Error saving your record",500);
	}
	
	//return the uuid
	$strUuid = $this->db->select(array('uuid'))->from('users')->where('id','=',$insertId);
	$strUuid = $strUuid->execute(false);
	$response->withJson($strUuid->fetch(),200);
	
});

/* *
 * Update User Details
 * */
$app->put('/user[/{uuid}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();
				
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson('Empty form!',500);
		return false;
	}

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['uuid']);
	if ( ! $userId ) {
		$response->withJson("Invalid User" ,500);
		return $response;
	}
	
	//password hashing
	if (!empty($data['password'])) {
		$options = ['cost' => 12,];
		$passwordFromPost = $data['password'];
		$data['password'] = password_hash($passwordFromPost, PASSWORD_BCRYPT, $options);
	}	
	
	// UPDATE users SET pwd = ? WHERE id = ?
	$updateStatement = $this->db->update( $data )
	                           ->table('users')
	                           ->where('uuid', '=', $args['uuid']);
	$blnResult = $updateStatement->execute(true);
	$response->withJson($blnResult,200);
		
});