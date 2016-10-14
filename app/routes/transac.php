<?php

/**
 * Submit Order
 **/
$app->post('/transac', function ($request, $response, $args) {
	
	$data = $request->getParsedBody();
		
	//check for data, return false if empty
	if ( empty($data) ) {
		return $response->withJson(array("status" => false, "message" => 'Empty Form!'), 404);
	}	
	
	//check items
	if (empty($data['items'])) {
		$response->withJson(array("status" => false, "message" => "No Item(s) Found!") , 404);
		return $response;		
	}  

	//check user if valid
	$strUserUuid = $this->jwtToken->user->uuid;
	$userId = $this->UserUtil->checkUser($strUserUuid);
	if ( ! $userId ) {
		$response->withJson(array("status" => false, "message" =>"Invalid User!"), 404);
		return $response;
	}
	$data['transac']['user_id'] = $userId;

	//check given price
	$blnPrice = $this->TransacUtil->checkPrices($data['items']);
	if(!$blnPrice) {
		$response->withJson(array("status" => false, "message" =>"Price Changes Detected!"), 404);
		return $response;		
	}
	
	//get the sub_total (add all amount in items as well as the add_ons total)
	$fltItemTotal = $fltAddOnTotal = 0;
	foreach ( $data['items'] as $value ) {
		$fltTotal = $value['qty'] * $value['price'];
		if (!empty($value['discount']))
			$fltTotal = $fltTotal - $value['discount'];

		//Calculate the add_ons total
		if(!empty($value['add_ons']) && count($value['add_ons']) > 0) {
			$fltAddOnTotal = 0;
			foreach ( $value['add_ons'] as $v ) {
				$fltAddOnTotal += ($v['price'] * $v['qty']);
			}
			
			$value['add_ons'] = json_encode($value['add_ons']);
		}		
		
		$value['total_amount'] = $fltTotal + $fltAddOnTotal;
		
		$arrNewDataItem[] = $value; 
		
		$fltItemTotal += $value['total_amount'];
	}	
	
	$data['items'] = $arrNewDataItem;
	$data['transac']['sub_total'] = $fltItemTotal;
	
	//check delivery cost
	if (!empty($data['transac']['address_id'])){
		$blnDeliveryCost = $this->TransacUtil->checkDeliveryCost($data['transac']['address_id'], $data['transac']['delivery_cost']); 
		if (!$blnDeliveryCost)
			return $response->withJson(array("status" => false, "message" =>"Delivery Cost Not Matched!"), 404);
	}
	
	//check promo code if exist or not expired. return the promo discount amount
	if (!empty($data['transac']['promo_code'])) {
		$blnDiscount = $this->TransacUtil->checkPromoCode($data['transac']['promo_code'], $data['transac']['discount']);
		if (!$blnDiscount)
			return $response->withJson(array("status" => false, "message" =>"Discount Not Matched!"), 404);		
	}
	
	//get the total amount
	$data['transac']['total_amount'] = $data['transac']['sub_total'] + $data['transac']['delivery_cost'] - $data['transac']['discount']; 
	$data['transac']['created'] = date('Y-m-d H:i:s');
	
	try {
		
		//set uuid		
		$data['transac']['uuid'] = uniqid();
		
		//insert into transactions table
		$arrFields = array_keys($data['transac']);
		$arrValues = array_values($data['transac']);
		$insertStatement = $this->db->insert( $arrFields )
								->into('transactions')
								->values($arrValues);
		$intTransacId = $insertStatement->execute(true);
		$strUuid = $this->db->select(array('uuid'))->from('transactions')->where('id','=',$intTransacId)->execute(false)->fetch();
		
		//insert into transaction_items
		foreach ( $data['items'] as $value ) {
			
			if (empty($value['add_ons']))  unset($value['add_ons']);
			
			$value['transaction_id'] = $intTransacId; 
			
			$value['discount'] = number_format( floatval($value['discount']), 2);
			$value['price'] = number_format($value['price'],2);
			
			$arrFields = array_keys($value);
			$arrValues = array_values($value);
// 			var_dump($value);
// 			die;
						
			//insert into items table
			$insertStatement = $this->db->insert( $arrFields )
										->into('transaction_items')
										->values($arrValues);
			$insertId = $insertStatement->execute(true);			
		}
		
		//send email to user
		$this->NotificationUtil->emailOrderStatus($data);
		
		//send email to vendor
		$this->NotificationUtil->emailOrderStatusVendor($data);
		
		return $response->withJson(array("status" => true, "data" =>$strUuid), 200);
				
	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);
		
	}

});

/**
 * Get Single Orders "order"
 **/
$app->get('/transac/order/{trasac_uuid}', function($request, $response, $args){

	//check user if valid
	$strUserUuid = $this->jwtToken->user->uuid;
	$userId = $this->UserUtil->checkUser($strUserUuid);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" =>"Invalid User!"), 404);
	}

	//check transac if valid
	$arrTransac = $this->TransacUtil->checkTransac($args['trasac_uuid'], $userId);
	if ( ! $arrTransac ) {
		return $response->withJson(array("status" => false, "message" =>"No Record(s) Found!"), 404);
	}
	
	try {
		
		//get items on this transactions
		$selectStmt = $this->db->select(array('transaction_items.*','menus.name'))->from('transaction_items')->where('transaction_id','=',$arrTransac['id']);
		$selectStmt = $selectStmt->join('menus', 'transaction_items.menu_id','=','menus.id');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();

		if (!empty($arrResult)) {
			foreach ($arrResult as $value) {
				if (!empty($value['add_ons'])) 
					$value['add_ons'] = json_decode($value['add_ons']);
				
				$arrNewResult[] = $value;
			}			

			$arrTransac['items'] = $arrNewResult;
		}			
			
		return $response->withJson(array("status" => true, "data" =>$arrTransac), 200);		
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);
		
	}

});

/**
 * Get All Orders of a user - "orders"
 **/
$app->get('/transac/orders/all[/{status}]', function($request, $response, $args){

	//check user if valid
	$strUserUuid = $this->jwtToken->user->uuid;
	$userId = $this->UserUtil->checkUser($strUserUuid);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" =>"Invalid User!"), 404);
	}
	
	$intStatus = isset($args['status']) ? $args['status'] : null;

	$arrTransac = $this->TransacUtil->checkTransac(null, $userId, $intStatus);
	if ( ! $arrTransac ) {
		return $response->withJson(array("status" => false, "message" =>"No Record(s) Found!"), 404);
	}
	
	try {
		
		//Extract transaction_id
		foreach ($arrTransac as $v) {
			$arrTransacId[] = $v['id'];
		}
		
		//get items on this transactions
		$selectStmt = $this->db->select(['transaction_items.*','menus.id `menu_id`'])->from('transaction_items')->whereIn('transaction_id', $arrTransacId );
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
			$vNew = [];
			$v['items'] = empty($arrResult[$v['id']]) ? "" : $arrResult[$v['id']]; 

			//json_decode add-ons
			if (!empty($v['items'])) {
				foreach ($v['items'] as $v1) {
					
					if (!empty($v1['add_ons'])) 
						$v1['add_ons'] = json_decode($v1['add_ons'], true);
					
					$vNew['items'][] = $v1;
				}
				$v['items'] = $vNew['items'];
			}
			
			$arrTransacNew[] = $v;
		}
		$arrTransac = $arrTransacNew;
		
		return $response->withJson(array("status" => true, "data" =>$arrTransac), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);

	}

});

/**
 * Update transaction_item status 
 * status:
 * - 0 = deferred
 * - 1 = pending
 * - 2 = preparing
 * - 3 = completed
 **/
$app->put('/transac/order/item/{trasac_uuid}', function($request, $response, $args){
	
	$data = $request->getParsedBody();
	
	//check user if valid
	$strUserUuid = $this->jwtToken->user->uuid;
	$userId = $this->UserUtil->checkUser($strUserUuid);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" =>"Invalid User"), 404);
	}	
	
	//check transac if valid
	$arrTransac = $this->TransacUtil->checkTransac($args['trasac_uuid'], $userId);
	if ( ! $arrTransac ) {
		return $response->withJson(array("status" => false, "message" =>"No Record(s) Found!"), 404);
	}	

	try {
			
		$updateStmt = $this->db->update( array('status' => $data['status']) )
								->table('transaction_items')
								->whereIn('id',$data['id'],'AND')
								->where('transaction_id', '=', $arrTransac['id']);
		$intCount = $updateStmt->execute();
		
		//if no rows updated
		if ( ! $intCount ) {
			return $response->withJson(array("status" => false, "message" =>"No Record(s) Found!"), 404);
		}
		
		//select the updated items
		$selectStmt = $this->db->select()->from('transaction_items')->whereIn('id',$data['id'],'AND')->where('transaction_id','=',$arrTransac['id']);
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();
		
		$arrResult['add_ons'] = json_decode($arrResult['add_ons']);
		
		return $response->withJson(array("status" => true, "data" =>$arrResult), 200);
		
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);
		
	}
	
});

/**
 * Update Order Transactions details
 * - address_id
 * - delivery_man_id
 * - status
 * 		0 - Cancelled
 * 		1 - Pending
 * 		2 - Order Accepted - Awaiting Payment
 * 		3 - Paid
 * 		4 - Preparing
 * 		5 - Dispatched - Delivery on the way
 * 		6 - Delivered - Order Complete
 * - transac_ref
 **/
$app->put('/transac/order/{trasac_uuid}', function($request, $response, $args){

	//allowed field to be updated
	$arrAllowedField = array('delivery_man_id','address_id','status','payment_ref');
	
	$data = $request->getParsedBody();
	
	//check status if valid
	if (!empty($data['status']) && (int)$data['status'] >= count((array)$this->manifest->status_order) )
		return $response->withJson(array("status" => false, "message" =>"Invalid Status Number"), 404);
		
		
	
	//check user if valid
	$strUserUuid = $this->jwtToken->user->uuid;
	$userId = $this->UserUtil->checkUser($strUserUuid);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" =>"Invalid User"), 404);
	}

	//check transac if valid
	$arrTransac = $this->TransacUtil->checkTransac($args['trasac_uuid'], $userId);
	if ( empty($arrTransac) ) {
		return $response->withJson(array("status" => false, "message" =>"No Record(s) Found!"), 404);
	}

	try {
		
		//make sure ONLY accept delivery_man_id / status / address_id		
		foreach ($data as $k => $v) {
			if (!in_array($k, $arrAllowedField))
				return $response->withJson(array("status" => false, "message" => "Update Not Allowed! - " . $k), 404);
		} 

		$data['modified'] = date('Y-m-d H:i:s');
		
		$updateStmt = $this->db->update( $data )
								->table('transactions')
								->where('id','=',$arrTransac['id']);
		$intCount = $updateStmt->execute();

		
		//if no rows updated
		if ( ! $intCount ) {
			return $response->withJson(array("status" => false, "message" =>"No Changes Made!"), 404);
		}
		
		//get items on this transactions
		$selectStmt = $this->db->select(array('transaction_items.*','menus.name'))->from('transaction_items')->where('transaction_id','=',$arrTransac['id']);
		$selectStmt = $selectStmt->join('menus', 'transaction_items.menu_id','=','menus.id');
		$selectStmt = $selectStmt->execute();
		$arrResult = $selectStmt->fetchAll();
		
		$arrOrderDetails['transac'] = $arrTransac; 
		
		if (!empty($arrResult)) {
			foreach ($arrResult as $value) {
				if (!empty($value['add_ons']))
					$value['add_ons'] = json_decode($value['add_ons'], true);
		
				$arrNewResult[] = $value;
			}
		
			$arrOrderDetails['items'] = $arrNewResult;
		}		
		
		if (!empty($data['status']))
			$arrOrderDetails['transac']['status'] = $data['status'];
		
		//send email to user
		$this->NotificationUtil->emailOrderStatus($arrOrderDetails);
		
		//send email to vendor
		$this->NotificationUtil->emailOrderStatusVendor($arrOrderDetails);		
		
		return $response->withJson(array("status" => true, "data" => $arrOrderDetails['transac']), 200);

	} catch (Exception $e) {

		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);

	}

});

/**
 * verifying the mobile payment on the server side
 * method - POST
 * @param paymentId paypal payment id
 * @param paymentClientJson paypal json after the payment
 **/
$app->post('/transac/paypal/verify/{transac_uuid}', function($request, $response, $args){
	
	$data = $request->getParsedBody();
	$paymentId = $data['payment_ref'];
	
	//check user if valid
	$strUserUuid = $this->jwtToken->user->uuid;
	$userId = $this->UserUtil->checkUser($strUserUuid);
	if ( ! $userId ) {
		return $response->withJson(array("status" => false, "message" =>"Invalid User!"), 404);
	}
		
	//check transac if valid	
	$arrTransac = $this->TransacUtil->checkTransac($args['transac_uuid'], $userId);
	if ( ! $arrTransac ) {
		return $response->withJson(array("status" => false, "message" =>"No Record(s) Found!"), 404);
	}
	
	try {
		
		$payment = $this->PaypalPayment->get($paymentId, $this->PaypalApiContext);
		
		if($payment->getState() === 'approved') {
			//update transaction status			
			
			$data['status'] = 2;//status 2 = paid
			$data['payment_ref'] = $payment->getId();
			
			$updateStmt = $this->db->update( $data )
									->table('transactions')
									->where('id','=',$arrTransac['id']);
			$intCount = $updateStmt->execute();

			return $response->withJson(array("status" => true, "data" => $data), 200);
			
		}	

		$data['payment_ref'] = $payment->getId();
		$data['status'] = $payment->getState();
		return $response->withJson(array("status" => true, "data" => $data), 200);
	
	} catch (Exception $e) {
		
		return $response->withJson(array("status" => false, "message" =>$e->getMessage()), 500);
		
	}
});

//Get promo discount

//Get delivery cost

//Set delivery man

//Set payment method