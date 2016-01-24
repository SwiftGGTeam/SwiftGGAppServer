<?php

require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');


class CatchController extends Controller {

    public function index() { 
    	$articleOpr = Flight::model(INTERFACE_SGAERTICLE);
    	$dir        = $_SERVER['DOCUMENT_ROOT'] . "/GGHexo/src/";
    	// 搜索目录下所有的文件和文件夹
		$rt         = ToolUtil::deepScanDir($dir);
		// 遍历读取所有文件
		foreach ($rt['file'] as $key => $value) {
			// 判断是否为 md 文件
			if(ToolUtil::getExtension($value) == 'md'){
				$content = ToolUtil::readFile($value);
				$matches = array();
				$data = array();
				if($content){
					// 解析标题
					preg_match('/title: ([\s\S]+?)[\s\S*?]date/',$content,$matches);
					$title = $matches[1];
					// 解析日期
					preg_match('/date: ([\s\S]+?)[\s\S*?]tags/' ,$content,$matches);
					$date  = $matches[1];
					// 解析标签
					preg_match('/tags: \[([\s\S]+?)\][\s\S*?]categories/' ,$content,$matches);
					$tags  = $matches;
					// 解析分类
					preg_match('/categories: ([\s\S]+?)[\s\S*?]permalink/' ,$content,$matches);
					$categories = $matches;
					// 解析固定链接
					preg_match('/permalink: ([\s\S]+?)[\s\S*?]---/' ,$content,$matches);
					$permalink = $matches[1];
					$data = array(
						'title'      => $title,
						'date'       => $date,
						'tags'       => $tags,
						'categories' => $categories,
						'permalink'  => $permalink
					);
					var_dump($data);
				}
			}
		}

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