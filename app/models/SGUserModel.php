<?php

require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'config/errorCode.inc.php');

class SGUserModel {

	public $errCode = 0;
    public $errMsg  = '';

    public $_dB = null; //数据库连接

    public function __construct()
    {
        $this->init();      
    }

    public function init(){
        
    }

    //清除错误信息
    public function _clearERR() {
        $this -> errCode = 0;
        $this -> errMsg = '';
    }

    //连接数据库
    public function _initDB() {       
        $this -> _dB = Flight::connectMysqlDB();
        if(!$this-> _dB){
            $this -> errMsg = "db error";
            $this -> errCode = GAME_ERR_DB_EXEC;
            return false;
        }
        return true;
    }

    //关闭数据库连接
    public function _closeDB(){
        Flight::closeMysqlDB();
    }

	// 插入一条数据，返回id
    public function addUser($dataArray) {

        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }

        $table  = DB_TABLE_USER;

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
        try{
            $field  = substr( $field,0,-1);
            $value  = substr( $value,0,-1);       
            $sql    = "insert into $table($field) values($value)";
            $result = mysql_query($sql);
            $id     = mysql_insert_id();
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $id;
    }

	// 获取一条记录
	public function getOneUserById($id) {
		$this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }

        $table  = DB_TABLE_USER;

        try{
            $query  = "SELECT * FROM " . $table . " WHERE id = " . $id;
            $result = mysql_query($query);
            $rt     = &mysql_fetch_array($result);
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $rt;
    }

	// 获取所有文章数据
	public function getAllUsers(){
		$this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }

        $table  = DB_TABLE_USER;

        try{
    		$query  = "SELECT * FROM " . $table;
    		$result = mysql_query($query);
    		$rt     = array();
    		$i      = 0;
    		while($row = mysql_fetch_array($result))
    		{
    		 	$rt[$i++]=$row;
    		}
		} catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $rt;
	}

    // 查询在数据库中是否含有该分类（名称），如没有就执行插入
    // @return id
    public function getIdByName($name){
        $articleTypeData = $this->getAllUsers();
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
        return $this->addUser($data);
    }


    // 带where条件的查询
    public function getUserByWhere($dataArray){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }

        $table  = DB_TABLE_USER;
        $where = "";

        if( !is_array($dataArray) || count($dataArray) <= 0) {
            $this->halt('没有要查询的数据');
            return false;
        }
        while(list($key,$val) = each($dataArray)) {
            $where .= "$key='$val' AND ";
        }
        $where  = substr($where, 0, -5);
        try{      
            $sql    = "SELECT * FROM $table WHERE " . $where;
            $result = mysql_query($sql);
            $rt     =& mysql_fetch_array($result);
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $rt;
    }

    // 查看库中是否存在
    public function isExistByUserName($userName){
        $data = $this->getAllUsers();
        foreach ($data as $key => $value) {
            if($value['account'] == $userName){
                return true;
            }
        }
        return false;
    }

}

?>