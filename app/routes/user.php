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
		
		//get user details
		$data = $this->UserUtil->getUserDetails($strUserUuid);
		
		if (!$data['user'])
			return $response->withJson(array('status'=>false, 'message'=>'User Not Found!'), 200); 
		
		//un-json the photo details
		if (!empty($data['user']['photo']))
			$data['user']['photo'] = json_decode($data['user']['photo'],true);
		
		//remove the password
		unset($data['user']['password']);
		
		return $response->withJson(array('status'=>true, 'data'=>$data) , 200);		
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 200);
		
	}

});

/**
 * Adding new user
 */
$app->post('/user/add', function ( $request, $response, $args) {
	
	$dataUser = $request->getParsedBody()['user'];
	$dataAddress = $request->getParsedBody()['address'];

	//check for data, return false if empty
	if (empty($dataUser)) {
		return $response->withJson(array('status'=>false,"message"=>'Empty Form!'), 204);
	}
	
	//check if email is valid
	if (filter_var($dataUser['email'], FILTER_VALIDATE_EMAIL) === false) {
		return $response->withJson(array('status'=>false,"message"=>'Invalid Email!'), 412);
	}
		
	//search email and number if it exist
	$isExist = $this->db->select(array('email'))->from('users')->where('email','=',$dataUser['email']);
	$isExist = $isExist->execute(false);
	if (!empty($isExist->fetch())) {
		return $response->withJson(array('status'=>false,"message"=>'User already exist!'), 409);				
	}

	//check for email and password if empty
	if (empty($dataUser['password']) || empty($dataUser['email'])) {
		$response->withJson(array('status'=>false,"message"=>'Email or Password must not be empty!'), 204);
		return $response;
	}
	
	//password hashing 
	if (!empty($dataUser['password'])) {
		$options = ['cost' => 12,];
		$passwordFromPost = $dataUser['password'];
		$dataUser['password'] = password_hash($passwordFromPost, PASSWORD_BCRYPT, $options);		
	}
	
	$dataUser['uuid'] = uniqid();

	$arrFields = array_keys($dataUser);
	$arrValues = array_values($dataUser);

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
		$this->NotificationUtil->emailUserNew($arrResult);
		
		//Adding address
		$dataAddress = $request->getParsedBody()['address'];
		if ($dataAddress) {
			$dataAddress['user_id'] = $insertId;
			$dataAddress['created'] = date('Y-m-d h:i:s');
			
			$arrFields = array_keys($dataAddress);
			$arrValues = array_values($dataAddress);
			
			try {
				// INSERT INTO address ( id , usr , pwd ) VALUES ( ? , ? , ? )
				$insertStatement = $this->db->insert( $arrFields )
											->into('addresses')
											->values($arrValues);
				$insertId = $insertStatement->execute(true);
			} catch (Exception $e) {
				return $response->withJson(array('status'=>false, "message"=> $e->getMessage() ), 500);
			}
		}
		
		//generate JWT token
		$token = $this->UserUtil->tokenized($arrResult);
		
		return $response->withJson(array('status'=>true, "data"=>$token),200);		
				
	} catch (Exception $e) {
		
		return $response->withJson(array('status'=>false, "message"=> $e->getMessage() ), 500);
		
	}

	
});

/* *
 * Update User Details
 * */
$app->put('/user/{uuid}', function ( $request, $response, $args) {

	$data = $request->getParsedBody();
				
	//check for data, return false if empty
	if (empty($data)) {
		return $response->withJson(array('status'=>false,"message"=>'Empty Form!'), 500);
	}
	
	$strUserUuid = $this->jwtToken->user->uuid;
		
	if ($args['uuid'] !== $strUserUuid && $isAdmin == false) 
		return $response->withJson(array('status'=>false, 'message'=>'Unauthorized Access!'), 401);	

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
		
		if (!empty($data['password'])) $data['password'] = "**********";
		
		//get user details
		$data = $this->UserUtil->getUserDetails($args['uuid']);
		
		return $response->withJson(array('status'=>true, "data"=>$data), 200);		
		
	} catch (Exception $e) {

		return $response->withJson(array('status'=>false, "message"=>$e->getMessage()),500);
		
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
 * Forget Password
 */
$app->post('/user/password/reset', function($request, $response, $args){
	$data = $request->getParsedBody();

	try {
		//get user details
		$selectUserStatement = $this->db->select(['password','id','email','first_name','last_name'])->from('users')->where('email','=',$data['email']);
		$stmt = $selectUserStatement->execute(false);
		$dataUser = $stmt->fetch();
		
		$strPassword = $this->UserUtil->generatePassword();
		$dataUser['password'] = password_hash($strPassword, PASSWORD_BCRYPT, ['cost' => 12]);
		
		if(!$dataUser) return $response->withJson(array('status'=>false, "message"=> "User Could Not Be Found!"), 404);
		
		$updateStatement = $this->db->update( $dataUser )
									->table('users')
									->where('id', '=', $dataUser['id']);
		$blnResult = $updateStatement->execute(true);

		if (!$blnResult)
			return $response->withJson(array('status'=>false, "message"=> "Error Generating Password"), 500);
		 
		//send email for the new temporary password
		if (!$this->NotificationUtil->emailUserResetPassword($dataUser, $strPassword)) 
			return $response->withJson(array('status'=>false, "message"=> "Opppss.. there seems to be an error!"), 404);
		
		return $response->withJson(array('status'=>true, "message"=> "Password has been reset successfully"), 200);
		
	} catch (Exception $e) {
		
		return $response->withJson(array('status'=>false, "message"=>$e->getMessage()), 500);
		
	}

	die;
});

/**
 * Verify sms
 */
$app->get('/user/verify/sms', function ($request, $response, $args){
	var_dump(md5(rand(0, 10)));die;
});