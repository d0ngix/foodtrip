<?php

/* *
 * Get list of Vendors
 * */
$app->get('/provider/', function($request, $response, $args){
	
	$arrLocation = $request->getQueryParams();
	
	try {
		
		$selectStmt = $this->db->select()->from('vendors');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll(); 
		
		if (!empty($arrResult)) {
			//un-json the photo details
			foreach ($arrResult as $v) {
				if (!empty($v['photo'])) {
					$v['photo'] = json_decode($v['photo'],true);
					$v['photo']['dimensions'] = json_decode($v['photo']['dimensions'],true);
					
					//unset the unnecessary
					unset($v['photo']['extension']);
					unset($v['photo']['mime']);
					unset($v['photo']['size']);
					unset($v['photo']['md5']);
				}
				
				//TODO: Check if Vendor is Open at the time of request
				$v['open'] = true;				
				
				$arrNewResult[] = $v;
			}
			$arrResult = $arrNewResult;
		}
		
		return $response->withJson(array("status" => true, "data" => $arrResult), 200);
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);
		
	}
	
});

/* *
 * Adding new Vendor
 * */
$app->post('/provider', function ( $request, $response, $args ) {

	$data = $request->getParsedBody();

	//check for data, return false if empty
	if (empty($data)) {
		//$response->setStatus(500);
		$response->withJson(array('status'=>false,"message"=>'Empty Form!'),500);
		return $response;
	}

	//search email and number if it exist
	$isExist = $this->db->select(array('email'))->from('vendors')->where('email','=',$data['email']);
	$isExist = $isExist->execute(false);
	if (!empty($isExist->fetch())) {
		$response->withJson(array('status'=>false,"message"=>'Record Already Exist!'),500);
		return $response;
	}

	$data['uuid'] = uniqid();

	$arrFields = array_keys($data);
	$arrValues = array_values($data);

	try {

		// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
		$insertStatement = $this->db->insert( $arrFields )
									->into('vendors')
									->values($arrValues);
		$insertId = $insertStatement->execute(true);

		//return the uuid
		$strUuid = $this->db->select(array('uuid'))->from('vendors')->where('id','=',$insertId);
		$strUuid = $strUuid->execute(false);

		return $response->withJson(array('status'=>true, "data"=>$strUuid->fetch()), 200);

	} catch (Exception $e) {

		return $response->withJson(array('status'=>false, "message"=> $e->getMessage() ), 200);

	}


});

/* *
 * Add Vendor Users
 * */
