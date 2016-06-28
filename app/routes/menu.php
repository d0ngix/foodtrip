<?php
/* *
 * Add New Menu
 * */
$app->post('/menu/{vendor_uuid}', function($request, $response, $args){

	$data = $request->getParsedBody();

	//check for data, return false if empty
	if (empty($data)) {
		return $response->withJson(array("status" => false, "message" => 'Empty form!'), 404);
	}

	//check vendor if valid
	$vendorId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( !is_int($vendorId) ) {
		$response->withJson($vendorId ,500);
		return $response;
	}

	//remove vendor_uuid
	unset($data['vendor_uuid']);
	$data['vendor_id'] = $vendorId;

	$arrFields = array_keys($data);
	$arrValues = array_values($data);

	try {

		// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
									->into('menus')
									->values($arrValues);
		$insertId = $insertStatement->execute(true);
		
		// Get the newly insert menu
		$selectStatement = $this->db->select()->from('menus')->where('id','=',$insertId);
		$selectStatement = $selectStatement->execute();
		$arrResult = $selectStatement->fetch(); 

		return $response->withJson(array("status" => true, "data" => $arrResult), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});

/* *
 * Get All Menu from Vendor
 * */
$app->get('/menu/all/{vendor_uuid}', function ($request, $response, $args) {

	//check vendor if valid
	$vendorId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( !is_int($vendorId) ) {
		$response->withJson(array("status" => false, "message" => 'Invalid Vendor!'), 404);
		return $response;
	}

	try {

		$selectStatement = $this->db->select()->from('menus')->whereMany(array('vendor_id' => $vendorId, 'is_active' => 1), '=');
		$stmt = $selectStatement->execute(false);
		$data = $stmt->fetchAll();

		$blnRatings = $this->MenuUtil->getRatings($data);

		if (!empty($data))
			$data = $this->MenuUtil->getMenuImages($data);

		return $response->withJson(array("status" => true, "data" => $data), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});

/* *
 * Get Single Menu from Vendor 
 * */
$app->get('/menu/{menu_id}', function ($request, $response, $args) {

	try {

		$selectStatement = $this->db->select()->from('menus')->where('id', '=', $args['menu_id']);
		$stmt = $selectStatement->execute(false);
		$data = $stmt->fetchAll();

		$blnRatings = $this->MenuUtil->getRatings($data);

		if (!empty($data))
			$data = $this->MenuUtil->getMenuImages($data);

		return $response->withJson(array("status" => true, "data" => $data), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);

	}

});

/* *
 * Rate a Menu w/ Comment
 * */
$app->post('/menu/rate/{menu_id}', function($request, $response, $args){

	$data = $request->getParsedBody();

	//check user if valid
	$userId = $this->UserUtil->checkUser($data['user_uuid']);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" => "Invalid User!"), 404);
	}
	$data['user_id'] = $userId;
	unset($data['user_uuid']);
	
	//check if user has an order of the menu item
	$selectStmt = $this->db->select(array('transaction_items.*'))->from('transactions')->whereMany(array('user_id' => $userId, 'menu_id' => $args['menu_id']), '=')
									->join('transaction_items', 'transaction_items.transaction_id', '=', 'transactions.id');
	$selectStmt = $selectStmt->execute();
	$arrResult = $selectStmt->fetchAll();
	if ( ! $arrResult ) {
		return $response->withJson(array("status" => false, "message" => "No Record(s) Found!"),404);
	}	
			
	//set rating
	$arrRatings = array( 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five');
	$data[$arrRatings[$data['rate']]] = 1;
	unset($data['rate']);	
	
	$data['menu_id'] = $args['menu_id'];
	
	$arrFields = array_keys($data);
	$arrValues = array_values($data);

	try {
		
		// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
									->into('menu_ratings')
									->values($arrValues);
		$insertId = $insertStatement->execute(true);

		//retrieve the menu details
		$selectStmt = $this->db->select()->from('menus')->where('id','=',$args['menu_id']);
		$selectStmt = $selectStmt->execute(false);
		$data = $selectStmt->fetchAll();

		$blnRatings = $this->MenuUtil->getRatings($data);

		return $response->withJson(array("status" => true, "data" => $data), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);
	}

});

/* *
 * Get Menu Ratings
 * */
$app->get('/menu/rate/{menu_id}', function ($request, $response, $args) {

	try {

		//retrieve the menu details
		$selectStmt = $this->db->select()->from('menus')->where('id','=',$args['menu_id']);
		$selectStmt = $selectStmt->execute(false);
		$data = $selectStmt->fetchAll();
		
		$ratings = $this->MenuUtil->getRatings($data);
		
		return $response->withJson(array("status" => true, "data" => $data), 200);		
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" => $e->getMessage()), 500);
		
	}


});