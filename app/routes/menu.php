<?php

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

$app->get('/menu[/{vendor_uuid}]', function ($request, $response, $args) {
	
	//check vendor if valid
	$vendorId = $this->UserUtil->checkVendor($args['vendor_uuid']);
	if ( !is_int($vendorId) ) {
		$response->withJson($vendorId ,500);
		return $response;
	}	
	
	try {
		
		$selectStatement = $this->db->select()->from('menus')->whereMany(array('vendor_id' => $vendorId, 'is_active' => 1), '=');
		$stmt = $selectStatement->execute(false);
		//var_dump($stmt->queryString);die;
		$data = $stmt->fetchAll();

		foreach ($data as $value)
			$id[] = $value['id'];

		$selectStatement = $this->db->select(array("menu_id","path", "is_primary", "name"))->from('menu_images')->whereIn('menu_id', $id);
		$stmt = $selectStatement->execute(false);
		$dataImage = $stmt->fetchAll();
	
		if (!empty($dataImage)) {
			foreach ($dataImage as $value) {
				$newDateImage[$value['menu_id']][] = $value;
			}
			$dataImage = $newDateImage;
			
			//var_dump($dataImage);die;
			$counter = 0;
			foreach ($data as $value) {
				$newData[$counter] = $value;
				if (!empty($dataImage[$value['id']]))  
					$newData[$counter]['photo'] = $dataImage[$value['id']];
				
				$counter++;
			}
			$data = $newData;
		}
		
		$response->withJson($data, 200);
				
	} catch (Exception $e) {
		
		$response->withJson($e->getMessage(), 200);
		
	}

	
	
		
});