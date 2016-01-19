<?php
/*
	根据不同的类型，维持自增id，获得最大的id
*/
class maxId {
	
	public function getNextSequence($type, $db){
		$table = 'ids';
		$update = array('$inc'=>array("id"=>1));
		$query = array('type'=>$type);
		$command = array(
        	'findandmodify'=>$table, 'update'=>$update,
        	'query'=>$query, 'new'=>true, 'upsert'=>true
    	);

		$ret = $db->command($command);
		//数据类型转换
		$id = (int)$ret['value']['id'] ->__toString();
		return $id;
	}
}


?>