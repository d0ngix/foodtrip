<?php namespace Utilities;

class MenuUtil
{
	public $db = null;

	public function __construct( $db = null ) {

		$this->db = $db;

	}

	//check if uuid for valid user
	public function getMenuImages ($data) {

		foreach ($data as $value) {
			if (!empty($value['menu_id']))
				$id[] = $value['menu_id'];
			else 
				$id[] = $value['id'];
		}

		$selectStatement = $this->db->select(array("photo","id"))->from('menus')->whereIn('id', $id);
		$stmt = $selectStatement->execute(false);
		$dataImage = $stmt->fetchAll();

		if (!empty($dataImage)) {
			foreach ($dataImage as $value) {
				$arrPhoto = json_decode($value['photo'], true);
				//$newDateImage[$value['id']] = '/' . $arrPhoto['path'] . '/' . $arrPhoto['name'];
				$newDateImage[$value['id']] = $arrPhoto;
			}
 			$dataImage = $newDateImage;

 			$counter = 0;

			foreach ($data as $value) {
				$newData[$counter] = $value;

				if (!empty($value['menu_id'])) {
					if (!empty($dataImage[$value['menu_id']]))
						$newData[$counter]['photo'] = $dataImage[$value['menu_id']];
				}
				
				$counter++;
			}
			
			return $newData;
		}
		return false;
	}

	public function getRatings (&$data) {

		if (empty($data)) return false;

		//get only the menu_id
		foreach ($data as $value) $menuId[] = $value['id'];

		try {
				
			$selectStmt = $this->db->select()->from('menu_ratings')->whereIn('menu_id', $menuId)->orderBy('menu_id','ASC');
			$selectStmt = $selectStmt->execute();
			$dataRatings = $selectStmt->fetchAll();
				
			//return false if menu has no ratings
			if (empty($dataRatings)) return false;
				
			reset($dataRatings);
			$firstKey = key($dataRatings);
			end($dataRatings);
			$lastKey = key($dataRatings);

			$prevMenuId = $intFive = $intFour = $intThree = $intTwo = $intOne = null;

			foreach ($dataRatings as $key => $value ) {

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
				$data = $arrData;
			}
				
			return true;
				
				
		} catch (Exception $e) {
				
			return $e->getMessage();
				
		}

	}
	
	public function getFeedback (&$data) {

		if (empty($data)) return false;
	
		try {
	
			$selectStmt = $this->db->select(array('menu_ratings.*', 'users.first_name'))->from('menu_ratings')->where('menu_id', '=', $data['id'])
											->join('users','users.id','=','menu_ratings.user_id')->orderBy('menu_ratings.id','ASC');
			$selectStmt = $selectStmt->execute();
			$dataRatings = $selectStmt->fetchAll();

			//return false if menu has no ratings
			if (empty($dataRatings)) return false;
	
			reset($dataRatings);
			$firstKey = key($dataRatings);
			end($dataRatings);
			$lastKey = key($dataRatings);			
			
			$prevMenuId = $intFive = $intFour = $intThree = $intTwo = $intOne = null;
			
			foreach ($dataRatings as $key => $value ) {

				extract($value);
				
				if ($one) $rate = 1;
				if ($two) $rate = 2;
				if ($three) $rate = 3;
				if ($four) $rate = 4;
				if ($five) $rate = 5;
								
				$arrFeedback[] = [
						'rate' => $rate, 
						'comment' => $value['comment'], 
						'first_name' => $value['first_name'], 
						'created' => $value['created']
				];
				
				//get the overall ratings
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
				$ratings = ( 5 * $intFive + 4 * $intFour + 3 * $intThree + 2 * $intTwo + 1 * $intOne ) / ( $intFive + $intFour + $intThree + $intTwo + $intOne );

			}
			
			$data['ratings'] = $ratings;
			$data['count'] = count($dataRatings);
			$data['feedback'] = $arrFeedback;
	
			return true;
	
	
		} catch (Exception $e) {
	
			return $e->getMessage();
	
		}
	
	}	
	
	public function checkUserOrder($intMenuId, $intUserId) {
		
		
		
	}

}