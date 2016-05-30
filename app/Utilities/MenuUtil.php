<?php namespace Utilities;

class MenuUtil
{
	public $db = null;
	
	public function __construct( $db = null ) {
		
		$this->db = $db;
			
	}	

	//check if uuid for valid user
	public function getMenuImages ($data) {
		
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
			return $newData;
		}
	
		return false;
	}

	public function getRatings ($data) {
		
		if (empty($data)) return false;
		
		//get only the menu_id
		foreach ($data as $value) $menuId[] = $value['id'];
		
		try {
			
			$selectStmt = $this->db->select()->from('menu_ratings')->whereIn('menu_id', $menuId)->orderBy('menu_id','ASC');
			$selectStmt = $selectStmt->execute();
			$data = $selectStmt->fetchAll();
			
			//return false if menu has no ratings 
			if (empty($data)) return false;
			
			reset($data);
			$firstKey = key($data);
			end($data);
			$lastKey = key($data);

			$prevMenuId = $intFive = $intFour = $intThree = $intTwo = $intOne = null;
			
			foreach ($data as $key => $value ) {
				
				extract($value);//extract = array('id' => 1) = $id = 1
				
				if ( $prevMenuId === $menu_id || $key === $firstKey) {
					
					$intFive += $five;
					$intFour += $four;
					$intThree += $three;
					$intTwo += $two;
					$intOne += $one;

					$prevMenuId = $menu_id;
					
					if ($key !== $lastKey) continue;
					
				} 
				
				//weighted average
				$ratings[$prevMenuId] = ( 5 * $intFive + 4 * $intFour + 3 * $intThree + 2 * $intTwo + 1 * $intOne ) / ( $intFive + $intFour + $intThree + $intTwo + $intOne );				

				$intFive = $intFour = $intThree = $intTwo = $intOne = null;
					
				$intFive += $five;
				$intFour += $four;
				$intThree += $three;
				$intTwo += $two;
				$intOne += $one;
				
				$prevMenuId = $menu_id;
								
			}
			

			if ($ratings) {
				foreach ($data as $key => $value) {
			
					if (isset($ratings[$value['id']]))
						$value['ratings'] = $ratings[$value['id']];
			
					$arrData[$key] = $value;
				}
			}
			
			return $ratings;
			
			
		} catch (Exception $e) {
			
			return $e->getMessage();
			
		}
		
	}
		
}