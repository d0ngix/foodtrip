<?php

/* *
 * Submit Order
 * */
$app->post('/transac/{user_uuid}', function ($request, $response, $args) {
	
	$dataBody = $request->getParsedBody();
	
	//check items
	if (empty($dataBody['items'])) {
		$response->withJson("No Item(s) Found!" ,500);
		return $response;		
	}  

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['user_uuid']);
	if ( ! $userId ) {
		$response->withJson("Invalid User" ,500);
		return $response;
	}
	$dataBody['transac']['user_id'] = $userId;

	//check given price
	$blnPrice = $this->TransacUtil->checkPrices($dataBody['items']);
	if(!$blnPrice) {
		$response->withJson("Price changes detected" ,500);
		return $response;		
	}
	
	//get the sub_total (add all amount in items)
	$fltItemTotal = 0;
	foreach ( $dataBody['items'] as $value ) {
		$fltTotal = $value['qty'] * $value['price'];
		if (!empty($value['discount']))
			$fltTotal = $fltTotal - $value['discount'];

		$fltItemTotal += $fltTotal;
	}	
	$dataBody['transac']['sub_total'] = $fltItemTotal;
	
	//check delivery cost
	if (!empty($dataBody['transac']['address_id'])){
		$blnDeliveryCost = $this->TransacUtil->checkDeliveryCost($dataBody['transac']['address_id'], $dataBody['transac']['delivery_cost']); 
		if (!$blnDeliveryCost)
			$response->withJson("Delivery Cost Not Matched!" ,500);
	}
	
	//check promo code if exist or not expired. return the promo discount amount
	if (!empty($dataBody['transac']['promo_code'])) {
		$blnDiscount = $this->TransacUtil->checkPromoCode($dataBody['transac']['promo_code'], $dataBody['transac']['discount']);
		if (!$blnDiscount)
			$response->withJson("Discount Not Matched!" ,500);		
	}
	
	//get the total amount
	$dataBody['transac']['total_amount'] = $dataBody['transac']['sub_total'] + $dataBody['transac']['delivery_cost'] - $dataBody['transac']['discount']; 
	
	//set uuid	
	//$dataBody['transac']['uuid'] = uniqid();
	
	try {
		
		$dataBody['transac']['uuid'] = uniqid();
		
		//insert into transactions table
		$arrFields = array_keys($dataBody['transac']);
		$arrValues = array_values($dataBody['transac']);
		$insertStatement = $this->db->insert( $arrFields )
								->into('transactions')
								->values($arrValues);
		$intTransacId = $insertStatement->execute(true);
		$strUuid = $this->db->select(array('uuid'))->from('transactions')->where('id','=',$intTransacId)->execute(false)->fetch();
		
		//insert into transaction_items
		foreach ( $dataBody['items'] as $value ) {
			
			$value['transaction_id'] = $intTransacId; 
			
			//get total amount each item
			$value['total_amount'] = $value['qty'] * $value['price'] - $value['discount'];
			
			$arrFields = array_keys($value);
			$arrValues = array_values($value);
			
			$insertStatement = $this->db->insert( $arrFields )
										->into('transaction_items')
										->values($arrValues);
			$insertId = $insertStatement->execute(true);			
		}
		
		$response->withJson($strUuid, 200);
				
	} catch (Exception $e) {

		$response->withJson($e->getMessage(), 500);
		
	}

	
	//insert into items table

});


/* *
 * Get Single Orders "order"
 * */
$app->get('/transac/order/{user_uuid}/{trasac_uuid}', function($request, $response, $args){

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['user_uuid']);
	if ( ! $userId ) {
		$response->withJson("Invalid User" ,500);
		return $response;
	}
		
	$arrTransac = $this->TransacUtil->checkTransac($args['trasac_uuid'], $userId);
	if ( ! $arrTransac ) {
		$response->withJson("No Record(s) Found!" ,500);
		return $response;
	}
	
	try {
		
		//get items on this transactions
		$selectStmt = $this->db->select()->from('transaction_items')->where('transaction_id','=',$arrTransac['id']);
		$selectStmt = $selectStmt->join('menus', 'transaction_items.menu_id','=','menus.id');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();
		
		//get menu images
		if (!empty($arrResult))
			$arrResult = $this->MenuUtil->getMenuImages($arrResult);
		
		$arrTransac['items'] = $arrResult;
		
		$response->withJson($arrTransac, 200);		
		
	} catch (Exception $e) {
		
		$response->withJson($e->getMessage(),500);
		
	}

});

/* *
 * Get All Orders of a user - "orders"
* */
$app->get('/transac/orders/{user_uuid}[/{status}]', function($request, $response, $args){

	//check user if valid
	$userId = $this->UserUtil->checkUser($args['user_uuid']);
	if ( ! $userId ) {
		$response->withJson("Invalid User" ,500);
		return $response;
	}
	
	$intStatus = isset($args['status']) ? $args['status'] : null;

	$arrTransac = $this->TransacUtil->checkTransac(null, $userId, $intStatus);
	if ( ! $arrTransac ) {
		$response->withJson("No Record(s) Found!" ,500);
		return $response;
	}
	
	try {
		
		//Extract transaction_id
		foreach ($arrTransac as $v) {
			$arrTransacId[] = $v['id'];
		}
		
		//get items on this transactions
		$selectStmt = $this->db->select()->from('transaction_items')->whereIn('transaction_id', $arrTransacId );
		$selectStmt = $selectStmt->join('menus', 'transaction_items.menu_id','=','menus.id');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();
		
		//get menu images
		if (!empty($arrResult)) {
			$arrResult = $this->MenuUtil->getMenuImages($arrResult);
		
			//make transaction_id as the key
			$arrResultNew = [];
			foreach ($arrResult as $v)
				$arrResultNew[$v['transaction_id']][] = $v; 	
		
			$arrResult = $arrResultNew;
		}
		
		//set the items of each transactions
		$arrTransacNew = [];
		foreach ($arrTransac as $v) {
			$v['items'] = $arrResult[$v['id']];
			$arrTransacNew[] = $v;
		}
		$arrTransac = $arrTransacNew;

		$response->withJson($arrTransac, 200);

	} catch (Exception $e) {

		$response->withJson($e->getMessage(),500);

	}

});


/* *
 * 
 * */


//Get promo discount

//Get delivery cost

//Set delivery man

//Set payment method