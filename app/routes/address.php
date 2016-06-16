<?php
//use Model\User;
//use Controller\UserController;

$app->get('/address[/{uuid}]', function ( $request, $response, $args) {

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['uuid']);
	if ( ! $userId ) {
		$response->withJson(array("status" => false, "message" => "Invalid User"), 404);
		return $response;
	}

	$selectUserStatement = $this->db->select()->from('addresses')->where('user_uuid','=',$args['uuid']);
	$stmt = $selectUserStatement->execute(false);
	$data = $stmt->fetchAll();
	
	return $response->withJson(array("status" => true, "data" => $data), 200);

});

$app->post('/address[/{uuid}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();

	//check for data, return false if empty
	if ( empty($data) ) {
		$response->withJson(array("status" => false, "message" => 'Empty form!'), 404);
		return $response;
	}

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['uuid']);
	if ( ! $userId ) {
		$response->withJson(array("status" => false, "message" => "Invalid User"), 404);
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
		return $response->withJson(array("status" => true, "data" => $insertId), 200);

	} catch (PDOException $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});

$app->put('/address[/{id}]', function ( $request, $response, $args) {

	$data = $request->getParsedBody();

	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		return $response->withJson(array("status" => false, "message" => 'Empty form!'), 404);
	}

	//check user if valid
	$userId = $this->UserUtil->checkUser($data['user_uuid']);
	if ( ! $userId ) {
		$response->withJson(array("status" => false, "message" => "Invalid User"), 404);
		return $response;
	}

	// UPDATE users SET pwd = ? WHERE id = ?
	$updateStatement = $this->db->update( $data )
								->table('addresses')
								->where('id', '=', $args['id']);

	$insertId = $updateStatement->execute(true);
	return $response->withJson(array("status" => true, "data" => $insertId), 200);

});


/* *
 * Delete Adddress
 * */
$app->delete('/address/{user_uuid}/{id}', function ( $request, $response, $args) {

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['user_uuid']);
	if ( ! $userId ) {
		$response->withJson(array("status" => false, "message" => "Invalid User"),500);
		return $response;
	}

	try {
		// DELETE FROM users WHERE id = ?
		$deleteStatement = $this->db->delete()
									->from('addresses')
									->whereMany(array('id'=>$args['id'], 'user_id' => $userId), '=');

		$affectedRows = $deleteStatement->execute();

		return $response->withJson(array("status" => true, "data" => $affectedRows), 200);

	} catch (PDOException $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});