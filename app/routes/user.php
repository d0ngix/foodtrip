<?php
//use Model\User;
//use Controller\UserController;

$app->get('/user[/{uuid}]', function ( $request, $response, $args) {
	
	try {

		$selectUserStatement = $this->db->select()->from('users')->where('uuid','=',$args['uuid']);
		$stmt = $selectUserStatement->execute(false);
		$data = $stmt->fetch();
		
		if (!$data)
			return $response->withJson(array('status'=>false, 'message'=>'Usre Not Found!'), 200); 
		
		//un-json the photo details
		if (!empty($data['photo']))
			$data['photo'] = json_decode($data['photo'],true);
		
		$response->withJson(array('status'=>false, 'data'=>$data) , 200);		
		
	} catch (Exception $e) {
		
		$response->withJson(array("status" => false, "message" => $e->getMessage()), 200);
		
	}
	


});

//Adding new user
$app->post('/user', function ( $request, $response, $args) {
	
	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson(array('status'=>false,"message"=>'Empty Form!'),500);
		return $response;
	}
	
	//search email and number if it exist
	$isExist = $this->db->select(array('email'))->from('users')->where('email','=',$data['email']);
	$isExist = $isExist->execute(false);
	
	if (!empty($isExist->fetch())) {
		$response->withJson(array('status'=>false,"message"=>'User already exist!'),500);
		return $response;		
	}

	//check for email and password if empty
	if (empty($data['password']) || empty($data['email'])) {
		$response->withJson(array('status'=>false,"message"=>'Email or Password must not be empty!'),500);
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

	try {
		
		// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
									->into('users')
									->values($arrValues);
		$insertId = $insertStatement->execute(true);
				
		//return the uuid
		$strUuid = $this->db->select(array('uuid'))->from('users')->where('id','=',$insertId);
		$strUuid = $strUuid->execute(false);
		$response->withJson(array('status'=>true, "data"=>$strUuid->fetch()), 200);		
		
	} catch (Exception $e) {
		
		$response->withJson(array('status'=>false, "message"=> $e->getMessage() ), 200);
		
	}

	
});

/* *
 * Update User Details
 * */
$app->put('/user[/{uuid}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();
				
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson(array('status'=>false,"message"=>'Empty Form!'),500);
		return false;
	}

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['uuid']);
	if ( ! $userId ) {
		$response->withJson(array('status'=>false,"message"=>"Invalid User!") ,500);
		return $response;
	}
	
	//password hashing
	if (!empty($data['password'])) {
		$options = ['cost' => 12,];
		$passwordFromPost = $data['password'];
		$data['password'] = password_hash($passwordFromPost, PASSWORD_BCRYPT, $options);
	}	
	
	try {
		
		$updateStatement = $this->db->update( $data )
									->table('users')
									->where('uuid', '=', $args['uuid']);
		$blnResult = $updateStatement->execute(true);
		$response->withJson(array('status'=>true, "data"=>$data),200);		
		
	} catch (Exception $e) {

		$response->withJson(array('status'=>false, "message"=>$e->getMessage()),200);
		
	}
	// UPDATE users SET pwd = ? WHERE id = ?

	
		
});