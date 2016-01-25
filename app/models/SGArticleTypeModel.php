<?php

require_once (APPLIB_PATH.'config/app.inc.php');

class SGArticleTypeModel {

	public $errCode = 0;
	public $errMsg  = '';

	public $tableName = 'sg_article_type';

	public function __construct()
	{
		$this->init();		
	}

	public function init(){
		
	}

	// 插入一条数据，返回id
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
            $field .= "$key,";
            $value .= "'$val',";
        }
        $field  = substr( $field,0,-1);
        $value  = substr( $value,0,-1);       
        $sql    = "insert into $this->tableName($field) values($value)";
        $result = mysql_query($sql);
        $id     = mysql_insert_id();
        // 关闭数据库
        Flight::closeMysqlDB();
        if(!$result) 
        	return false;
        return $id;
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