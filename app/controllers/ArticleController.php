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
        if(empty($categoryListData)){
            $this->errReturn('分类列表为空，请联系后台人员修复');
        }
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
    			//'coverUrl' => $value['cover_url'],
    			'coverUrl' => 'http://i8.tietuku.com/1a055c782b5a4c37.png',
    			'sum'      => $value['sum']
    		);
    		$data[$key] = $list;
    	}
    	$response = array(
    		'ret'  => 0,
    		'data' => $data
    	);
    	return $this->ajaxReturn($response);
    }

    // v1版 对应分类的文章
    public function getArticlesByCategoryV1(){
    	if(empty($_POST['categoryId'])){
    		// 为空则输出所有文章
    		$articleList = $this->articleOpr->get_all();
            if(empty($articleList)){
                $this->errReturn('文章列表为空，请联系后台人员修复');
            }
    	}else{
    		// 按照分类id输出
    		$arrayId = $this->articleTypeOpr->get_all_by_typeId($_POST['categoryId']);
            if(empty($arrayId)){
                $this->errReturn('请求参数有误，无法找到该分类id');
            }
    		foreach ($arrayId as $key => $value) {
    			$articleList[$key] = $this->articleOpr->get_one_by_id($value['type_id']);
    		}
    	}
    	// 封装数据
    	foreach ($articleList as $key => $value) {
			$list = array(
				'id'             => $value['id'],
				// 'coverUrl'       => $value['cover_url'],
				'coverUrl'       => 'http://i8.tietuku.com/1a055c782b5a4c37.png',
				// 'authorImageUrl' => $value['author_image'],
				'authorImageUrl' => 'http://i8.tietuku.com/1a055c782b5a4c37.png',
				'submitData'     => date('Y-m-d H:i:s' , $value['updated_time']),
				'title'          => $value['title'],
				'articleUrl'     => "http://swift.gg/" . date('Y/m/d',$value['updated_time']) . '/' .$value['permalink'],//$value['content_url'],
				'translator'     => $value['translator'],
				'starsNumber'    => $value['stars_number'],
				'commentsNumber' => rand(0,80)
			);
			$data[$key] = $list;
		}
		// 封装数据
    	$response = array(
    		'ret'  => 0,
    		'data' => $data
    	);
    	return $this->ajaxReturn($response);
    }

    // v1版 文章详情
    public function getDetailV1(){
		$this->errReturn('接口已废除');
    }

    // 错误返回接口
    public function errReturn($errMsg){
        // 封装数据
        $response = array(
            'ret'    => -1,
            'errMsg' => $errMsg,
        );
        return $this->ajaxReturn($response);
    }
}

