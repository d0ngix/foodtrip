<?php
//use Model\User;
//use Controller\UserController;

$app->get('/address[/{uuid}]', function ( $request, $response, $args) {

	//check user if valid
	$blnValidUser = $this->UserUtil->checkUser($args['uuid']);
	if ( $blnValidUser !== true) {
		$response->withJson($blnValidUser ,500);
		return $response;
	}
	
	$selectUserStatement = $this->db->select()->from('addresses')->where('user_uuid','=',$args['uuid']);
	$stmt = $selectUserStatement->execute(false);
	$data = $stmt->fetchAll();
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
	
	//check user if valid
	$blnValidUser = $this->UserUtil->checkUser($args['uuid']);
	if ( $blnValidUser !== true) {
		$response->withJson($blnValidUser ,500);
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
	
});

$app->put('/address[/{id}]', function ( $request, $response, $args) {
	
	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson('Empty form!',500);
		return false;
	}
	
	//check user if valid
	$blnValidUser = $this->UserUtil->checkUser($this->db, $data['user_uuid']);
	if ( !$blnValidUser ) {
		$response->withJson('Invalid User!',500);
		return $response;
	}	
	
	// UPDATE users SET pwd = ? WHERE id = ?
	$updateStatement = $this->db->update( $data )
	                           ->table('addresses')
	                           ->where('id', '=', $args['id']);
		
	
	$insertId = $updateStatement->execute(true);
	$response->withJson($insertId,200);
		
});

$app->delete('/address[/{id}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();

	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson('Empty form!',500);
		return false;
	}

	//check user if valid
	$blnValidUser = $this->UserUtil->checkUser($this->db, $data['user_uuid']);
	if ( !$blnValidUser ) {
		$response->withJson('Invalid User!',500);
		return $response;
	}

	try {
		// DELETE FROM users WHERE id = ?
		$deleteStatement = $this->db->delete()
		                           	->from('addresses')
		                           	->where('id', '=', $args['id']);
		
		$affectedRows = $deleteStatement->execute();
	
		$response->withJson($affectedRows,200);
	
	} catch (PDOException $e) {
	
		$response->withJson($e->getMessage(), 404);
	
	}

});