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
    public function insert($typeId, $tag, $title, $coverUrl, $contentUrl, $translator, $proofreader, $finalization, $author, $authorImage, $originalDate, $originalUrl, $clickedNumber = 0) {
    	$currentTime = time();
        $field  = "type_id,tag,title,cover_url,content_url,translator,proofreader,finalization,author,author_image,original_date,original_url,clicked_number,created_time,updated_time";
        $value  = "$typeId,$tag,$title,$coverUrl,$contentUrl,$translator,$proofreader,$finalization,$author,$authorImage,$originalDate,$originalUrl,$clickedNumber,$currentTime,$currentTime";
        Flight::connectMysqlDB();
        $sql    = "insert into $this->tableName($field) values($value)";
        $result = mysql_query($sql);
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