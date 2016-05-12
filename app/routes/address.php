<?php
//use Model\User;
//use Controller\UserController;

$app->get('/address[/{uuid}]', function ( $request, $response, $args) {

	$selectUserStatement = $this->db->select()->from('addresses')->where('user_uuid','=',$args['uuid']);
	$stmt = $selectUserStatement->execute(false);
	$data = $stmt->fetch();
	$response->withStatus(200);
	$response->withJson($data);

});

$app->post('/address[/{uuid}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();

	//check for data, return false if empty
	if ( empty($data) ) {
		$response->withJson('Empty form!',500);
		return $response;
	}
	
	var_dump($this->checkUser($args['uuid']));die;
	
	//check if uuid for valid user
	$isUserExist = $this->db->select(array('count(id) "count"'))->from('users')->where('uuid','=',$args['uuid']);
	$isUserExist = $isUserExist->execute()->fetch();
	if (!$isUserExist['count']) {
		$response->withJson('Invalid User!',500);
		return $response;		
	}
	
	$data['user_uuid'] = $args['uuid'];
	$data['created'] = date('Y-m-d h:i:s');
	
	$arrFields = array_keys($data);
	$arrValues = array_values($data);

	try {
		// INSERT INTO address ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
								->into('addresses')
								->values($arrValues);

		$insertId = $insertStatement->execute(true);
		$response->withJson($insertId, 200);
				
	} catch (PDOException $e) {
		
		$response->withJson($e->getMessage(), 404);
		
	}

	
		
	//$response->withJson($insertId,200);
	
});

$app->put('/address[/{id}]', function ( $request, $response, $args) {
	
	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson('Empty form!',500);
		return false;
	}
	
	//search email and number if it exist
	$isExist = $this->db->select()->from('users')->where('id' , '=' , $args['id']);
	$isExist = $isExist->execute(false);
	
	if (empty($isExist->fetch())){
		$response->withJson('User Not Found!',500);
		return false;
	}
	
	// UPDATE users SET pwd = ? WHERE id = ?
	$updateStatement = $this->db->update( $data )
	                           ->table('users')
	                           ->where('id', '=', $args['id']);
		
	
	$insertId = $updateStatement->execute(true);
	$response->withJson($insertId,200);
		
});