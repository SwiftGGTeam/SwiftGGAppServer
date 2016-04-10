<?php
// //namespace controllers;
// use \Flight;
// use \Controller;

require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'config/errorCode.inc.php');
require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');

/*
 *  文章模块接口
 */
class ArticleController extends Controller {

	// 文章 Model 操作
	protected $articleOpr;
	// 类型 Model 操作
    protected $typeOpr;

    public function __construct(){
    	// 初始化
		$this->articleOpr     = Flight::model(INTERFACE_SGARTICLE);
		$this->typeOpr        = Flight::model(INTERFACE_SGTYPE);
    }

    // v1版 获取分类列表
    public function getCategoryListV1(){
        // 写log
        $this->writeLog('getCategoryListV1');

    	$categoryListData = $this->typeOpr->getAllTypes();
        if(empty($categoryListData)){
            $this->errReturn(TYPES_IS_NULL, '分类列表为空');
        }
    	// 封装数据
    	$data = array();
    	foreach ($categoryListData as $key => $value) {
    		$list = array(
    			'id'       => (int)$value['id'],
    			'name'     => $value['name'],
    			//'coverUrl' => $value['cover_url'],
    			'coverUrl' => 'http://i8.tietuku.com/1a055c782b5a4c37.png',
    			'sum'      => (int)$value['sum']
    		);
    		$data[$key] = $list;
    	}
    	return $this->sucReturn($data);
    }

    // v1版 对应分类的文章
    public function getArticlesByCategoryV1(){
        // 写log
        $txt = json_encode($_POST);
        $this->writeLog('getArticlesByCategoryV1：' . $txt);

    	if(empty($_POST['categoryId'])){

    		// 为空则输出所有文章
    		$articleList = $this->articleOpr->getAllArticles();
            if(empty($articleList)){
                $this->errReturn(TYPES_IS_NULL, '分类列表为空');
            }
    	}else{

            // 判断是否含有特殊字符
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_POST['categoryId'])){
                $this->errReturn(CATEGORYID_SPECIAL_CHARACTER,'含有特殊字符');
            }

    		// 按照分类id输出
    		$articleList = $this->articleOpr->getArticlesByTypeId($_POST['categoryId']);
    	    if(!$articleList){
                $this->errReturn(TYPE_IS_NOT_EXIST, '分类Id不存在');
            }
        }
    	// 封装数据
    	foreach ($articleList as $key => $value) {
			$list = array(
				'id'             => (int)$value['id'],
				// 'coverUrl'       => $value['cover_url'],
				'coverUrl'       => 'http://i8.tietuku.com/1a055c782b5a4c37.png',
				// 'authorImageUrl' => $value['author_image'],
				'authorImageUrl' => 'http://i8.tietuku.com/1a055c782b5a4c37.png',
				'submitData'     => date('Y-m-d H:i:s' , $value['updated_time']),
				'title'          => $value['title'],
				'articleUrl'     => "http://swift.gg/" . date('Y/m/d',$value['updated_time']) . '/' .$value['permalink'],//$value['content_url'],
				'translator'     => $value['translator'],
				'description'    => $value['description'],
                'starsNumber'    => (int)$value['stars_number'],
				'commentsNumber' => rand(0,80)
			);
			$data[$key] = $list;
		}
		return $this->sucReturn($data);
    }

    // v1版 文章详情
    public function getDetailV1(){
		$this->errReturn('接口已废除');
    }
}

