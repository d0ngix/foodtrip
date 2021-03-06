<?php

/**
 * Get list of Vendors
 **/
$app->get('/provider', function($request, $response, $args){
	
	$arrLocation = $request->getQueryParams();

	extract($arrLocation);

	/**
	 * Get all nearest retaurant from the user flow
	 * - Get all vendors within 1KM radius using Haversine Formula
	 * 		ref: https://developers.google.com/maps/articles/phpsqlsearch_v3?csw=1
	 * - Query to Google Maps the travel time duration from restaurant to user using Google Distance Matrix API 
	 * 		ref: https://developers.google.com/maps/documentation/distance-matrix/intro
	 *   
	 **/
	
	try {

		/**
		 * - Haversine Formula
		 * SELECT address, name, lat, lng, 
		 * 		( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance 
		 * FROM markers 
		 * 		HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20	
		 **/
		$arrSelect = [
			'vendor_id', 'uuid', 'address_name', 'latitude', 'longitude', 'operating_hours',
			"( 3959 * acos( cos( radians($lat) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($long) ) + sin( radians($lat) ) * sin( radians( latitude ) ) ) ) AS distance"
		];
		$selectStmt = $this->db->select($arrSelect)->from('vendor_addresses')
													->whereMany(['deleted' => '0', 'active' => '1'], '=')
													//->having('distance','<', $rad)
													->orderBy('distance');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();
		
		if (empty($arrResult)) 
			return $response->withJson(array("status" => false, "message" => "Oooppss! We could not find any restaurant near your address."), 404);

		$intVendorId = $arrAddress = [];
		foreach ($arrResult as $k => $v) {
			$intVendorId[] = $v['vendor_id'];
			$arrAddress[$v['vendor_id']] = $v;
		}

		//Get the vendors list
		$selectStmt = $this->db->select()->from('vendors')->whereIn('id',$intVendorId)->where('deleted','=','0');		
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();		
		
		if (!empty($arrResult)) {
			//un-json the photo details
			foreach ($arrResult as $v) {
				
				$arrMeetUpLocation = null;
				
				if (!empty($v['photo'])) {
					$v['photo'] = json_decode($v['photo'],true);
					
					/*					
					if (!empty($v['photo']['dimensions']))
						$v['photo']['dimensions'] = @json_decode($v['photo']['dimensions'],true);
					*/
					
					//unset the unnecessary
					unset($v['photo']['extension']);
					unset($v['photo']['mime']);
					unset($v['photo']['size']);
					unset($v['photo']['md5']);
				}
				
				//flag if vendor is open
				$v['open'] = false;
				
				//TODO: Check if Vendor is Open at the time of request
				if (!empty($arrAddress[$v['id']]['operating_hours'])) {
					$vendorAddress =$arrAddress[$v['id']];
					$arrSched = json_decode($vendorAddress['operating_hours'], true);
					if ( !empty($arrSched) ) {
						//compare current day/time if open
						$strDay = strtolower(date('D'));
						array_walk($arrSched, function ($i, $k) use (&$strDay, &$v) {
							if(!empty($i[$strDay])) {
								$timeNow = time();//current server time
								$timeStart = date('U', strtotime($i[$strDay]['start']));
								$timeEnd = date('U', strtotime($i[$strDay]['end']));
								
								if ($timeStart <= $timeNow && $timeNow <= $timeEnd) {
									$v['open'] = true;
								}
								$v['open'] = true;//Alway Open
								
								$v['operating_hours']['start'] = $i[$strDay]['start'];
								$v['operating_hours']['end'] = $i[$strDay]['end'];
	 							//var_dump("$timeStart <= $timeNow <= $timeEnd");
	 							
								
							} 
						});

						//set meetup location selection list
						foreach ( $arrSched as $v1 ) {
							if ( key($v1)  === 'freetext' && !empty($v1[key($v1)]) ) {
								$arrMeetUpLocation[] = $v1[key($v1)];
								$v['meetup'][] = $v1[key($v1)];
								continue;
							}
							
							if (!empty($v1[key($v1)]['loc'])) {
								$arrMeetUpLocation[] = ucfirst(key($v1)) . ' ' . $v1[key($v1)]['start'] . ' - ' . $v1[key($v1)]['end'] . ' @ ' . $v1[key($v1)]['loc'];
								$v['meetup'][] = ucfirst(key($v1)) . ' ' . $v1[key($v1)]['start'] . ' - ' . $v1[key($v1)]['end'] . ' @ ' . $v1[key($v1)]['loc'];
							}
						}//endforeach
						
					}//endif

				}//Endif 
				
				$v['address']['uuid'] = $arrAddress[$v['id']]['uuid'];
				$v['address']['lat'] = $arrAddress[$v['id']]['latitude'];
				$v['address']['long'] = $arrAddress[$v['id']]['longitude']; 
				$v['address']['address_name'] = $arrAddress[$v['id']]['address_name'];

				$arrNewResult[] = $v;
			}//endforeach
			
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
