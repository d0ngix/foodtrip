<?php

/* *
 * Get User Addresses
 * */
$app->get('/provider/address/{uuid}', function ( $request, $response, $args) {

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['uuid']);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" => "Invalid User"), 404);
	}

	try {
		
		$selectStatement = $this->db->select()->from('addresses')->where('user_id','=',$userId);
		$selectStatement = $selectStatement->execute(false);
		$data = $selectStatement->fetchAll();
		
		return $response->withJson(array("status" => true, "data" => $data), 200);		
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);
		
	}

});

/* *
 * Add Address
 * */
$app->post('/provider/address/{vendor_uuid}', function ( $request, $response, $args) {

	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if ( empty($data) ) {
		return $response->withJson(array("status" => false, "message" => 'Empty Form!'), 404);
	}

	//check user if valid
	$intId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( ! $intId ) {
		$response->withJson(array("status" => false, "message" => "Invalid Vendor"), 404);
		return $response;
	}
	
	if (!empty($data['operating_hours'])) {
		$data['operating_hours'] = json_encode($data['operating_hours']);
	}

	$data['vendor_id'] = $intId;
	$data['created'] = date('Y-m-d h:i:s');

	$arrFields = array_keys($data);
	$arrValues = array_values($data);
	
	try {
		// INSERT INTO address ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
									->into('vendor_addresses')
									->values($arrValues);
		$insertId = $insertStatement->execute(true);
		
		$selectStmt = $this->db->select()->from('vendor_addresses')->where('id','=',$insertId);
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetch();

		return $response->withJson(array("status" => true, "data" => $arrResult), 200);

	} catch (PDOException $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});

/* *
 * Update Address
 * */
$app->put('/provider/address/{vendor_uuid}/{id}', function ( $request, $response, $args) {

	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		return $response->withJson(array("status" => false, "message" => 'Empty form!'), 404);
	}

	//check user if valid
	$userId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" => "Invalid Vendor"), 404);
	}
	
	//check if the user owns the address_id
	$selectStmt = $this->db->select()->from('vendor_addresses')->whereMany(array('id'=>$args['id'], 'vendor_id'=>$userId ),'=')->execute();
	$arrAddress = $selectStmt->fetch();
	if(!$arrAddress) {
		return $response->withJson(array("status" => false, "message" => "Vendor Address Not Found!"), 404);		
	}
	
	try {
		
		// UPDATE users SET pwd = ? WHERE id = ?
		$updateStatement = $this->db->update( $data )
									->table('vendor_addresses')
									->where('id', '=', $args['id']);
		$updateBln = $updateStatement->execute(true);
		
		//Get the address details
		$selectStmt = $this->db->select()->from('vendor_addresses')->where('id','=',$args['id'])->execute();
		$arrResult = $selectStmt->fetch();
		
		return $response->withJson(array("status" => true, "data" => $arrResult), 200);
		
	} catch (Exception $e) {
	
		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);
		
	}

});


/* *
 * Delete Adddress
 * */
$app->delete('/provider/address/{vendor_uuid}/{id}', function ( $request, $response, $args) {

	//check user if valid
	$userId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" => "Invalid Vendor"),500);
	}

	try {
		
		//check if the user owns the address_id
		$selectStmt = $this->db->select()->from('vendor_addresses')->whereMany(array('id'=>$args['id'], 'vendor_id'=>$userId ),'=')->execute();
		$arrAddress = $selectStmt->fetch();
		if(!$arrAddress) {
			return $response->withJson(array("status" => false, "message" => "Vendor Address Not Found!"), 404);
		}		
		
		// DELETE FROM users WHERE id = ?
		$deleteStatement = $this->db->delete()
									->from('vendor_addresses')
									->whereMany(array('id'=>$args['id'], 'vendor_id' => $userId), '=');

		$affectedRows = $deleteStatement->execute();

		return $response->withJson(array("status" => true, "data" => $affectedRows), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});