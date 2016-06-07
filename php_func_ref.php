<?php
//array_walk
$data = array('id' => 1, 'name' => 'John Doe');
array_walk($data, function ( &$arrData, $key, $id ){
	$arrData = array_intersect_key($arrData, array_flip($id) );
}, array('id'));
var_dump($data);
//---------------------------
//use - is to use outside (outscope) variables e.g. $message
$message = 'hello';
$example = function () use (&$message) {
	$message .= " World";
};
$example();
var_dump($message);
//---------------------------
//weighted average
$ratings = ( 5 * $intFive + 4 * $intFour + 3 * $intThree + 2 * $intTwo + 1 * $intOne ) / ( $intFive + $intFour + $intThree + $intTwo + $intOne );
//---------------------------
//foreach loop - determine first and last iteration
reset($data);
$firstKey = key($data);
end($data);
$lastKey = key($data);
foreach($data as $key => $element) {
	if ($key === $firstKey)
		echo 'FIRST ELEMENT! - ' . $key;
	
	if ($key === $lastKey);
		echo 'LAST ELEMENT! - ' . $key;
}
//---------------------------
//Insert to table with array_key is the column name and array_values is the value 
$arrFields = array_keys($data);
$arrValues = array_values($data);

// INSERT INTO users ( id , usr , pwd ) VALUES ( ? , ? , ? )
$insertStatement = $this->db->insert( $arrFields )
						->into('users')
						->values($arrValues);
$insertId = $insertStatement->execute(true);
//---------------------------
//Get value in an array
$arrMenuId = [];
array_walk($data['items'], function ($v, $k) use (&$arrMenuId) {
	$arrMenuId[] = $v['menu_id'];
});
//---------------------------
//return query params
$selectStmt->debugDumpParams();
//---------------------------