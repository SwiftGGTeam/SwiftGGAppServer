<?php

require_once (APPLIB_PATH.'config/app.inc.php');

class SGArticleModel {

	public $errCode = 0;
	public $errMsg  = '';

	public $tableName = 'sg_article';

	public function __construct()
	{
		$this -> init();		
	}

	public function init(){
		
	}

	// 插入一条数据
    public function insert($dataArray) {
    	// 连接数据库	
    	Flight::connectMysqlDB();
    	$field = "";
        $value = "";
        if( !is_array($dataArray) || count($dataArray) <= 0) {
            $this->halt('没有要插入的数据');
            return false;
        }
        while(list($key,$val) = each($dataArray)) {
            $field .="$key,";
            $value .="'$val',";
        }
        $field  = substr( $field,0,-1);
        $value  = substr( $value,0,-1);       
        $sql    = "insert into $this->tableName($field) values($value)";
        $result = mysql_query($sql);
        // 关闭数据库
        Flight::closeMysqlDB();
        if(!$result) 
        	return false;
        return true;
    }

	// 获取一条记录
	public function get_one_by_typeId($typeId) {
		Flight::connectMysqlDB();
        $query  = "SELECT * FROM " . $this->tableName . " WHERE typeId = " . $typeId;
        $result = mysql_query($query);
        $rt     =& mysql_fetch_array($result);
        Flight::closeMysqlDB();
        return $rt;
    }

	// 获取所有文章数据
	public function get_all(){
		Flight::connectMysqlDB();
		$query  = "SELECT * FROM " . $this->tableName;
		$result = mysql_query($query);
		$rt     = array();
		$i      = 0;
		while($row = mysql_fetch_array($result))
		{
		 	$rt[$i]=$row;
            $i++;
		}
		Flight::closeMysqlDB();
		return $rt;
	}

}

?>