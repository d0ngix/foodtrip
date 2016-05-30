<?php

//Add New Menu
$app->post('/menu', function($request, $response, $args){
	
	$data = $request->getParsedBody();
	
	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson('Empty form!',500);
		return $response;
	}
	
	//check vendor if valid
	$vendorId = $this->UserUtil->checkVendor($data['vendor_uuid']);
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
				
		$response->withJson($insertId, 200);
				
	} catch (Exception $e) {
		
		$response->withJson($e->getMessage(),404);
				
	}
	
});

//Get All Menu from Vendor
$app->get('/menu/all/{vendor_uuid}', function ($request, $response, $args) {
	
	//check vendor if valid
	$vendorId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( !is_int($vendorId) ) {
		$response->withJson($vendorId ,500);
		return $response;
	}	
	
	try {
		
		$selectStatement = $this->db->select()->from('menus')->whereMany(array('vendor_id' => $vendorId, 'is_active' => 1), '=');
		$stmt = $selectStatement->execute(false);
		$data = $stmt->fetchAll();
		

		$ratings = $this->MenuUtil->getRatings($menuId);
		
		var_dump($arrData);
		die;
			

		if (!empty($data))
			$data = $this->MenuUtil->getMenuImages($data);
		
		$response->withJson($data, 200);
				
	} catch (Exception $e) {
		
		$response->withJson($e->getMessage(), 200);
		
	}
		
});


//Get Single Menu from Vendor
$app->get('/menu/{menu_id}', function ($request, $response, $args) {
		
	try {
		
		$selectStatement = $this->db->select()->from('menus')->where('id', '=', $args['menu_id']);
		$stmt = $selectStatement->execute(false);		
		$data = $stmt->fetchAll();
		
		if (!empty($data))
			$data = $this->MenuUtil->getMenuImages($data);

		$response->withJson($data, 200);
		
	} catch (Exception $e) {
		
		$response->withJson($e->getMessage(), 200);
		
	}
	
});

//Rate a Menu w/ Comment
$app->post('/menu/rate/{user_uuid}', function($request, $response, $args){
	
	$data = $request->getParsedBody();
	
	//check user if valid
	$userId = $this->UserUtil->checkUser($args['user_uuid']);
	if ( ! $userId ) {
		$response->withJson("Invalid User" ,500);
		return $response;
	}
	$data['user_id'] = $userId;
	
	$arrFields = array_keys($data);
	$arrValues = array_values($data);
	
	try {
		// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
									->into('menu_ratings')
									->values($arrValues);
		$insertId = $insertStatement->execute(true);
		
		$ratings = $this->MenuUtil->getRatings($data['menu_id']);
				
		$data['ratings'] = $ratings;

		$response->withJson($data, 200);
		
	} catch (Exception $e) {
		
		$response->withJson($e->getMessage(), 200);
	}
	
});

$app->get('/menu/rate/{menu_id}', function ($request, $response, $args) {
	
	$ratings = $this->MenuUtil->getRatings($args['menu_id']);
	
	$response->withJson($ratings, 200);
	
});