<?php
require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'config/errorCode.inc.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');
class SGTypeModel {
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
    public function AddType($dataArray) {
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_TYPE;
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
        try{
            $sql    = "insert into $table($field) values($value)";
            $result = mysql_query($sql) or die('sql语句执行失败，错误信息是：' . mysql_error());
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
    public function getOneTypeById($id) {
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_TYPE;
        try{
            $query  = "SELECT * FROM " . $table . " WHERE id = " . $id;
            $result = mysql_query($query);
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
    // 获取所有文章数据
    public function getAllTypes(){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $articleTypeTable  = DB_TABLE_ARTICLE_TYPE;
        $typeTable  = DB_TABLE_TYPE;
        try{
            $query  = "SELECT * FROM $typeTable LEFT JOIN (SELECT `type_id`,count(*) AS sum FROM $articleTypeTable group BY `type_id`) b ON $typeTable.`id`=b.`type_id` ORDER BY `sum` DESC";
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
    // 获取对应类型的文章总数
    public function getAllArticlesNumber(){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_ARTICLE_TYPE;
        try{
            $query = "SELECT type_id,count(*) AS sum FROM $table group BY type_id";
            $result = mysql_query($query) or die('sql语句执行失败，错误信息是：' . mysql_error());
            $rt     = array();
            $i      = 0;
            while($row = mysql_fetch_array($result))
            {
                $rt[$i++]=$row;
            }
        }  catch (Exception $e) {
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
        $retArray = array();
        $articleTypeData = $this->getAllTypes();
        foreach ($articleTypeData as $key => $value) {
            // 找到该项，返回id
            if($value['name'] == $name || str_replace(' ','',$value['name']) == str_replace(' ','',$name)){
                $retArray = array('id' => $value['id'] , 'isAddType' => false);
                return $retArray;
            }
        }
        // 封装数据
        $data = array(
            'name'         => trim($name),
            'cover_url'    => "",
            'created_time' => time(),
            'updated_time' => time()
        );
        $retArray = array('id' => $this->AddType($data) , 'isAddType' => true);
        return $retArray;
    }
    // 查看库中是否存在
    public function isExistById($typeId){
        $data = $this->getAllTypes();
        foreach ($data as $key => $value) {
            if($value['id'] == $typeId){
                return true;
            }
        }
        return false;
    }

    // 获取最新文章的提交时间
    public function getLastCreatedTime(){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_TYPE;

        try{
            $query  =  "SELECT * FROM " . $table . " order by created_time DESC";
            $result =  mysql_query($query);
            $rt     =  &mysql_fetch_array($result);
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $rt['created_time'];
    }

}
?>
