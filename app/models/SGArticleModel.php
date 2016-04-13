<?php
require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'config/errorCode.inc.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');
class SGArticleModel {
    public $errCode = 0;
    public $errMsg  = '';
    public $_dB = null; //数据库连接
    private $typeOpr = null; //分类操作
    public function __construct()
    {
        $this->init();      
    }
    public function init(){
        $this->typeOpr = Flight::model(INTERFACE_SGTYPE);
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
    // 插入文章信息
    public function addArticle($dataArray) {
        $this -> _clearERR();
        // 获取分类ID
        $type = $dataArray['type'];
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        unset($dataArray['type']);
        $table = DB_TABLE_ARTICLE;
        $field = "";
        $value = "";
        if( !is_array($dataArray) || count($dataArray) <= 0) {
            $this->halt('没有要插入的数据');
            return false;
        }
        while(list($key,$val) = each($dataArray)) {
            $val   = mysql_escape_string($val);
            $field .="$key,";
            $value .="'$val',";
        }
        $field  = substr( $field,0,-1);
        $value  = substr( $value,0,-1); 
        try{
            $value  = str_replace("(",'\(',$value);
            $value  = str_replace(")",'\)',$value);
            $sql    = "insert into $table($field) values($value)";
            $result = mysql_query($sql);
            $id     = mysql_insert_id();
            foreach ($type as $value) {
                $articleType = array(
                    'type_id' => $value,
                    'article_id' => $id
                );
                $this -> addArticleAndTypeRelation($articleType);
            }
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $id;
    }
    // 添加文章和分类的关系
    public function addArticleAndTypeRelation($dataArray){
        $table = DB_TABLE_ARTICLE_TYPE;
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
        try{      
            $sql    = "insert into $table($field) values($value)";
            $result = mysql_query($sql);
            $id     = mysql_insert_id();
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        return $id;
    }
    // 获取一条记录
    public function getArticlesByTypeId($typeId) {
        $this -> _clearERR();
        // 查看id是否存在
        if(!$this->typeOpr->isExistById($typeId)){
            return false;
        }
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $articleTable     = DB_TABLE_ARTICLE;
        $articleTypeTable = DB_TABLE_ARTICLE_TYPE;
        $typeTable        = DB_TABLE_TYPE;
        try{
            $query  = "SELECT $articleTable.* FROM $articleTypeTable INNER JOIN $articleTable ON $articleTable.`id`=$articleTypeTable.`article_id` inner JOIN $typeTable ON $typeTable.`id`=$articleTypeTable.`type_id` WHERE $typeTable.`id`=$typeId ORDER BY `updated_time` DESC";
            $result = mysql_query($query);
            $rt     = array();
            $i      = 0;
            while($row = mysql_fetch_array($result))
            {
                $rt[$i++] = $row;
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

    // 获取一条记录
    public function getArticlesByTypeIdLimit($typeId,$pageIndex,$pageSize) {
        $this -> _clearERR();
        // 查看id是否存在
        if(!$this->typeOpr->isExistById($typeId)){
            return false;
        }
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $articleTable     = DB_TABLE_ARTICLE;
        $articleTypeTable = DB_TABLE_ARTICLE_TYPE;
        $typeTable        = DB_TABLE_TYPE;
        try{
            $query  = "SELECT $articleTable.* FROM $articleTypeTable INNER JOIN $articleTable ON $articleTable.`id`=$articleTypeTable.`article_id` inner JOIN $typeTable ON $typeTable.`id`=$articleTypeTable.`type_id` WHERE $typeTable.`id`=$typeId ORDER BY `created_time` DESC LIMIT " . ($pageIndex-1) * $pageSize . "," . $pageSize;
            $result = mysql_query($query);
            $rt     = array();
            $i      = 0;
            while($row = mysql_fetch_array($result))
            {
                $rt[$i++] = $row;
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

    // 获取所有文章数据
    public function getAllArticles(){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_ARTICLE;
        try{
            $query  = "SELECT * FROM " . $table . " order by created_time DESC";
            $result = mysql_query($query);
            $rt     = array();
            $i      = 0;
            while($row = mysql_fetch_array($result))
            {
                $rt[$i++] = $row;
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

    // 获取所有文章数据
    public function getAllArticlesLimit($pageIndex,$pageSize){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_ARTICLE;
        try{
            $query  = "SELECT * FROM " . $table . " order by created_time DESC LIMIT " . ($pageIndex-1) * $pageSize . "," . $pageSize;
            $result = mysql_query($query);
            $rt     = array();
            $i      = 0;
            while($row = mysql_fetch_array($result))
            {
                $rt[$i++] = $row;
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

    // 获取一条记录通过id
    public function getOneArticleById($id) {
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_ARTICLE;
        try{
            $query  =  "SELECT * FROM " . $table . " WHERE id = " . $id;
            $result =  mysql_query($query);
            $rt     =  &mysql_fetch_array($result);
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $rt;
    }

    // 获取最新文章的提交时间
    public function getLastUpdatedTime(){
        $this -> _clearERR();
        if(!$this->_initDB())
        {
            $this-> _closeDB();
            return false;
        }
        $table  = DB_TABLE_ARTICLE;
        
        try{
            $query  =  "SELECT * FROM " . $table . " order by updated_time DESC";
            $result =  mysql_query($query);
            $rt     =  &mysql_fetch_array($result);
        } catch (Exception $e) {
            $this -> errCode = GAME_ERR_DB_EXEC;
            $this -> errMsg = $e;
            $this -> _closeDB();
            return false;
        }
        $this -> _closeDB();
        return $rt['updated_time'];
    }

    // 查看库中是否存在该名字的一项
    public function isExistByTitle($title){
        $data = $this->getAllArticles();
        foreach ($data as $key => $value) {
            if($value['title'] == $title){
                return $value['id'];
            }
        }
        return false;
    }
}
?>