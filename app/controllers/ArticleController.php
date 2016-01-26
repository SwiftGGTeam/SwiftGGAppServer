<?php
// //namespace controllers;
// use \Flight;
// use \Controller;

require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');


class ArticleController extends Controller {

	// 文章 Model 操作
	protected $articleOpr;
	// 类型 Model 操作
    protected $typeOpr;
    // 文章中间表 Model 操作
    protected $articleTypeOpr; 

    public function __construct(){
    	// 初始化
		$this->articleOpr     = Flight::model(INTERFACE_SGARTICLE);
		$this->typeOpr        = Flight::model(INTERFACE_SGTYPE);
		$this->articleTypeOpr = Flight::model(INTERFACE_SGARTICLETYPE);
    }

    // v1版 获取分类列表
    public function getCategoryListV1(){
    	$categoryListData = $this->typeOpr->get_all();
    	$categorySumArticleData = $this->articleTypeOpr->get_sum_clickednumber("type_id");
    	// 加入总数
    	foreach ($categorySumArticleData as $sumArticleData) {
    		foreach ($categoryListData as $key => $listData) {
    			if($sumArticleData["type_id"] == $listData['id']){
    				$categoryListData[$key]['sum'] = $sumArticleData['sum'];
    			}
    		}
    	}
    	// 封装数据
    	$data = array();
    	foreach ($categoryListData as $key => $value) {
    		$list = array(
    			'id'       => $value['id'],
    			'name'     => $value['name'],
    			'coverUrl' => $value['cover_url'],
    			'sum'      => $value['sum']
    		);
    		$data[$key] = $list;
    	}
    	$response = array(
    		'ret'  => 0,
    		'data' => $data
    	);
    	//echo $response;
    	return $this->ajaxReturn($response);
    }

    // v1版 对应分类的文章
    public function getArticlesByCategoryV1(){
    	echo 'ArticleController:getArticlesByCategoryV1';
    }

    // v1版 文章详情
    public function getDetailV1(){
    	echo 'ArticleController:getDetailV1';
    }

}

