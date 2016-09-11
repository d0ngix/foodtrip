<?php

/**
 * Get User Details
 */
$app->get('/user/{uuid}', function ( $request, $response, $args) {
	//check if the requestor is sys-admin
	$isAdmin = false;
	
	try {
		//TODO: Only the owner, system admin and vendor can call Get User Details
		
		$strUserUuid = $this->jwtToken->user->uuid;
		
		if ($args['uuid'] !== $strUserUuid && $isAdmin == false) 
			return $response->withJson(array('status'=>false, 'message'=>'Unauthorized Access!'), 401);
		
		$selectUserStatement = $this->db->select()->from('users')->where('uuid','=',$strUserUuid);
		$stmt = $selectUserStatement->execute(false);
		$data = $stmt->fetch();
		
		if (!$data)
			return $response->withJson(array('status'=>false, 'message'=>'User Not Found!'), 200); 
		
		//un-json the photo details
		if (!empty($data['photo']))
			$data['photo'] = json_decode($data['photo'],true);
		
		//remove the password
		unset($data['password']);
		
		return $response->withJson(array('status'=>true, 'data'=>$data) , 200);		
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 200);
		
	}

});

/**
 * Adding new user
 */
$app->post('/user/add', function ( $request, $response, $args) {
	
	$data = $request->getParsedBody();

	//check for data, return false if empty
	if (empty($data)) {
		return $response->withJson(array('status'=>false,"message"=>'Empty Form!'), 204);
	}
	
	//check if email is valid
	if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
		return $response->withJson(array('status'=>false,"message"=>'Invalid Email!'), 412);
	}
		
	//search email and number if it exist
	$isExist = $this->db->select(array('email'))->from('users')->where('email','=',$data['email']);
	$isExist = $isExist->execute(false);
	if (!empty($isExist->fetch())) {
		return $response->withJson(array('status'=>false,"message"=>'User already exist!'), 409);				
	}

	//check for email and password if empty
	if (empty($data['password']) || empty($data['email'])) {
		$response->withJson(array('status'=>false,"message"=>'Email or Password must not be empty!'), 204);
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
		$arrResult = $this->db->select()->from('users')->where('id','=',$insertId);
		$arrResult = $arrResult->execute()->fetch();
		unset($arrResult['password']);
		
		//Send email ntifiaciton
		$this->NotificationUtil->emailNewUser($arrResult);
		
		//generate JWT token
		$token = $this->UserUtil->tokenized($arrResult);
		
		return $response->withJson(array('status'=>true, "data"=>$token),200);		
				
	} catch (Exception $e) {
		
		return $response->withJson(array('status'=>false, "message"=> $e->getMessage() ), 200);
		
	}

	
});

/* *
 * Update User Details
 * */
$app->put('/user[/{uuid}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();
				
	//check for data, return false if empty
	if (empty($data)) {
		return $response->withJson(array('status'=>false,"message"=>'Empty Form!'), 500);
	}

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['uuid']);
	if ( ! $userId ) {
		return $response->withJson(array('status'=>false,"message"=>"Invalid User!") ,500);
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
		
		return $response->withJson(array('status'=>true, "data"=>$data), 200);		
		
	} catch (Exception $e) {

		return $response->withJson(array('status'=>false, "message"=>$e->getMessage()),200);
		
	}
		
});

/**
 * User Login
 */
$app->post('/user/login', function ($request, $response, $args) {

	$data = $request->getParsedBody();

	try {
		
		//search for the user
		$selectStmt = $this->db->select()->from('users')->where('email','=',$data['email']);
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetch();		

		//verify the password
		if (!password_verify($data['password'], $arrResult['password'])) {
			return $response->withJson(array('status'=>false, "message"=> 'Invalid Username or Password!'), 401);
		}

		unset($arrResult['password']);
				
		//return $response->withJson(array('status'=>true, "data"=>$arrResult),200);
		
		//generate JWT token
		$token = $this->UserUtil->tokenized($arrResult);
		
		return $response->withJson(array('status'=>true, "data"=>$token),200);		
		
	} catch (Exception $e) {
		
		return $response->withJson(array('status'=>false, "message"=>$e->getMessage()), 500);
		
	}

});

/**
 * Verify email
 */
$app->get('/user/verify/email', function ($request, $response, $args){
	
	$data = $request->getQueryParams();

	$data['email'] = base64_decode($data['email']);
	//$data['verified'] = 0;

	try {
		
		//Get the user details based on email and the hash
		$selectStmt = $this->db->select()->from('users')->whereMany($data,'=');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetch();
		
		if (empty($arrResult)) 
			return $response->withJson(array('status'=>false, "message"=> "User Could Not Be Found!"), 404);
		elseif ($arrResult['verified'])
			return $response->withJson(array('status'=>false, "message"=> "We already know your not a spammer."), 200);
		
		$arrVerificationDetails = [
			'date_time' => date('c'),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'ip_address' => $_SERVER['REMOTE_ADDR'],
			'query_string' => $_SERVER['QUERY_STRING']
		];
		
		$updateStmt = $this->db->update( array('verified' => 1, 'verification_details' => json_encode($arrVerificationDetails) ) )
								->table('users')
								->where('id', '=', $arrResult['id']);
		$blnResult = $updateStmt->execute(true);
			
		if (empty($blnResult)) 
			return $response->withJson(array('status'=>false, "message"=> "Verification Error!"), 404);
			
		//Redirecto to Verification Successful
		echo "Hooraayyyy! You are real!";
		die;
		 
	} catch (Exception $e) {
		
		return $response->withJson(array('status'=>false, "message"=>$e->getMessage()), 500);
	
	}
	

});

/**
 * Verify sms
 */
$app->get('/user/verify/sms', function ($request, $response, $args){
	var_dump(md5(rand(0, 10)));die;
});