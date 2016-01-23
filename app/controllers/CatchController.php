<?php

require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');


class CatchController extends Controller {

    public function index() { 
    	$articleOpr = Flight::model(INTERFACE_SGAERTICLE);
		// 请求文章列表
		$articleListResponse = json_decode($this->catchArticleList());
		if($articleListResponse == '') die;
		foreach ($articleListResponse as $key => $value) {
			$value = (array)$value;
			// 请求文章信息
			$articleDetailsResponse = json_decode($this->catchArticleProperty($value['name']));
			ToolUtil::p($articleDetailsResponse) . '<br>';
			sleep(200);
		}
		echo 'OK';
    }

    // 获取文章列表
	public function catchArticleList(){
		$url     = "https://api.github.com/repos/SwiftGGTeam/source/contents/_posts/";
	 	$reponse = NetUtil::cURLHTTPSGet($url,20);
	 	return $reponse;
    }

    // 获取文章信息（单个文章）
    public function catchArticleProperty($fileName){
    	$url     = "https://api.github.com/repos/SwiftGGTeam/source/contents/_posts/" . $fileName ."?ref=master";
	 	$reponse = NetUtil::cURLHTTPSGet($url,20);
	 	return $reponse;
    }

}

?>