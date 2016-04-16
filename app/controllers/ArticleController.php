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
    			'coverUrl' => 'http://swiftggapp.b0.upaiyun.com/appicon.png',
    			'sum'      => (int)$value['sum']
    		);
    		$data[$key] = $list;
    	}
    	return $this->sucReturn($data);
    }

    // v1版 对应分类的文章
    public function getArticlesByCategoryV1(){
      // 写log
      $txt = json_encode($_GET);
      $this->writeLog('getArticlesByCategoryV1：' . $txt);

    	if(empty($_GET['categoryId'])){
				// 为空则输出所有文章
        if(!empty($_GET['pageIndex']) && !empty($_GET['pageSize'])){
            // 判断是否含有特殊字符
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_GET['pageIndex'])){
                $this->errReturn(PAGEINDEX_SPECIAL_CHARACTER,'含有特殊字符');
            }
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_GET['pageSize'])){
                $this->errReturn(PAGEINDEX_SPECIAL_CHARACTER,'含有特殊字符');
            }
						// 通过分页获取文章列表
            $articleList = $this->articleOpr->getAllArticlesLimit($_GET['pageIndex'], $_GET['pageSize']);
        }else{
					  // 通过所有文章列表
            $articleList = $this->articleOpr->getAllArticles();
        }
        if(empty($articleList)){
            //$this->errReturn(TYPES_IS_NULL, '分类列表为空');
            return $this->sucReturn("");
        }
    	}else{
        // 判断是否含有特殊字符
        if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_GET['categoryId'])){
            $this->errReturn(CATEGORYID_SPECIAL_CHARACTER,'含有特殊字符');
        }
        // 为空则输出所有文章
        if(!empty($_GET['pageIndex']) && !empty($_GET['pageSize'])){
            // 判断是否含有特殊字符
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_GET['pageIndex'])){
                $this->errReturn(PAGEINDEX_SPECIAL_CHARACTER,'含有特殊字符');
            }
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_GET['pageSize'])){
                $this->errReturn(PAGEINDEX_SPECIAL_CHARACTER,'含有特殊字符');
            }
						// 通过分页获取文章列表
            $articleList = $this->articleOpr->getArticlesByTypeIdLimit($_GET['categoryId'],$_GET['pageIndex'], $_GET['pageSize']);
        }else{
		    	// 按照分类id输出
		    	$articleList = $this->articleOpr->getArticlesByTypeId($_GET['categoryId']);
	    	}
        if(!$articleList){
            //$this->errReturn(TYPE_IS_NOT_EXIST, '分类Id不存在');
            return $this->sucReturn("");
        }
      }
			//ToolUtil::p($articleList);die;
    	// 封装数据
    	foreach ($articleList as $key => $value) {
				$list = array(
					'id'                 => (int)$value['id'],
					'typeId'             => (int)$value['type_id'],
					'typeName'           => $value['type_name'],
					// 'coverUrl'        => $value['cover_url'],
					'coverUrl'           => 'http://swiftggapp.b0.upaiyun.com/appicon.png',
					// 'authorImageUrl'  => $value['author_image'],
					'authorImageUrl'     => 'http://swiftggapp.b0.upaiyun.com/appicon.png',
					'submitDate'         => date(DATE_ISO8601, $value['created_time']),
					'title'              => $value['title'],
					'contentUrl'         => "http://swift.gg/" . date('Y/m/d',$value['updated_time']) . '/' .$value['permalink'],//$value['content_url'],
					'translator'         => $value['translator'],
					'articleDescription' => $value['description'],
	        'starsNumber'        => (int)$value['stars_number'],
					'commentsNumber'     => 0,
	        'updateDate'         => $value['updated_time']
				);
				$data[$key] = $list;
			}
			return $this->sucReturn($data);
    }

    // v1版 文章详情
    public function getDetailV1(){
			// 写log
			$txt = json_encode($_GET);
			$this->writeLog('getDetailV1：' . $txt);
			if(!empty($_GET['articleId'])){
				// 判断是否含有特殊字符
				if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$_GET['articleId'])){
						$this->errReturn(CATEGORYID_SPECIAL_CHARACTER,'含有特殊字符');
				}
				$article = $this->articleOpr->getOneArticleById($_GET['articleId']);
				if(empty($article)){
					return $this->sucReturn("");
				}
				$data = array(
					'id'                 => (int)$article['id'],
					'typeId'             => (int)$article['type_id'],
					'typeName'           => $article['type_name'],
					'tags'               => json_decode($article['tags']),
					'coverUrl'           => 'http://swiftggapp.b0.upaiyun.com/appicon.png',
					'contentUrl'     		 => "http://swift.gg/" . date('Y/m/d',$article['created_time']) . '/' .$article['permalink'],
					'translator'         => $article['translator'],
					'proofreader'        => $article['proofreader'],
					'finalization'       => $article['finalization'],
					'author'             => $article['author'],
					'authorImageUrl'     => 'http://swiftggapp.b0.upaiyun.com/appicon.png',
					'originalDate'       => $article['original_date'],
					'originalUrl'        => $article['original_url'],
					'articleDescription' => $article['description'],
					'clickedNumber'      => (int)$article['clicked_number'],
					'submitDate'         => date('Y-m-d H:i:s' , $article['created_time']),
					'starsNumber'        => (int)$article['stars_number'],
					'commentsNumber'     => 0,
					'content'            => $article['content'],
					'comments'           => array(),
	        'updateDate'         => $article['updated_time']
				);
				return $this->sucReturn($data);
			}else{
				$this->errReturn(ARTICLEID_IS_NULL, '文章id为空');
			}
    }
}
