<?php

require_once (APPLIB_PATH.'config/app.inc.php');

class SGUserModel {

	public $errCode = 0;
	public $errMsg  = '';

	public $tableName = 'sg_user';

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

	// 获取一条记录
	public function get_one_by_id($id) {
		Flight::connectMysqlDB();
        $query  = "SELECT * FROM " . $this->tableName . " WHERE id = " . $id;
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

    // 查询在数据库中是否含有该分类（名称），如没有就执行插入
    // @return id
    public function get_id_by_name($name){
        $articleTypeData = $this->get_all();
        foreach ($articleTypeData as $key => $value) {
            // 找到该项，返回id
            if($value['name'] == $name || str_replace(' ','',$value['name']) == str_replace(' ','',$name)){
                return $value['id'];
            }
        }
        // 封装数据
        $data = array(
            'name'         => trim($name),
            'cover_url'    => "",
            'created_time' => time(),
            'updated_time' => time()
        );
        return $this->insert($data);
    }


    // 带where条件的查询
    public function get_one_by_where($dataArray){
        // 连接数据库    
        Flight::connectMysqlDB();
        $where = "";
        if( !is_array($dataArray) || count($dataArray) <= 0) {
            $this->halt('没有要查询的数据');
            return false;
        }
        while(list($key,$val) = each($dataArray)) {
            $where .= "$key='$val' AND ";
        }
        $where  = substr($where, 0, -5);      
        $sql    = "SELECT * FROM $this->tableName WHERE " . $where;
        $result = mysql_query($sql) or die('sql语句执行失败，错误信息是：' . mysql_error());
        $rt     =& mysql_fetch_array($result);
        Flight::closeMysqlDB();
        return $rt;
    }

}

?>