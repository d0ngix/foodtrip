<?php namespace Utilities;

class TransacUtil {

	public $db = null;

	public function __construct( $db = null ) {

		$this->db = $db;

	}

	public function checkPrices($data) {

		if (empty($data)) return false;

		//get all the menu id
		foreach ($data as $value) $arrMenuId[] = $value['menu_id']; 

		//get the menus and check if the price where altered
		$selectStmt = $this->db->select(array('id','price','discount'))->from('menus')->whereIn('id',$arrMenuId);
		$selectStmt = $selectStmt->execute();
		$arrDataMenu = $selectStmt->fetchAll();
		
		if (!empty($arrDataMenu)) {
			foreach ($arrDataMenu as $value) $arrNewData[$value['id']] = $value;
			$arrDataMenu = $arrNewData;
		}		

		
		foreach ( $data as $value ) {
			//var_dump($value);
			if ( abs($value['price']) !== abs($arrDataMenu[$value['menu_id']]['price']) ) 
				return false;
		}
		
		return true;
	}
	
	//check if uuid for valid transaction
	public function checkTransac ($strUuid, $intUserId, $intStatus = null) {

		$arrWhere = [];
		if (!empty($strUuid))
			$arrWhere['uuid'] = $strUuid;
		if(!empty($intUserId))
			 $arrWhere['user_id'] = $intUserId;
		if(!empty($intStatus))
			$arrWhere['status'] = $intStatus;

		$selectStmt = $this->db->select(['transactions.*','vendors.name as vendor_name'])->from('transactions');
		$selectStmt = $selectStmt->join('vendors','transactions.vendor_id','=','vendors.id');
		$selectStmt = $selectStmt->whereMany($arrWhere,'=');
		$selectStmt = $selectStmt->execute();
		
		if (!empty($strUuid)) //Single Transaction
			$arrResult = $selectStmt->fetch();
		else 
			$arrResult = $selectStmt->fetchAll();
		
		return $arrResult;
	
	}	
	
	public function checkDeliveryCost($intAddressId , $fltDeliveryCost = null) {
		
		return true;
		
	}
	
	public function checkPromoCode($strPromoCode , $fltDiscount = null) {
	
		// find promo code and get the discount percentage
		// calculate the discount
		// $fltDiscount is not empty, compare the discount
		// return false if mismatched. else true
		
		return true;
	
	}	
}