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
					if($matches != NULL) {
						$title = $matches[1];
						// 去掉双引号
						$title = str_replace('"','',$title); 
						// 去掉换行
						$title = str_replace("\n",'',$title); 
					}
					// 解析日期
					preg_match('/date: ([\s\S]+?)[\s\S*?]tags/' ,$content,$matches);
					if($matches != NULL) {
						$date = $matches[1];
						// 去掉换行
						$date = str_replace("\n",'',$date); 
					}
					// 解析标签
					preg_match('/tags: \[([\s\S]+?)\][\s\S*?]categories/' ,$content,$matches);
					if($matches != NULL) {
						$tags = $matches[1];
						// 去掉换行
						$tags = str_replace("\n",'',$tags); 
					}	
					// 解析分类
					preg_match('/categories: \[([\s\S]+?)\][\s\S*?]permalink/' ,$content,$matches);
					if($matches != NULL) {
						$categories = $matches[1];
						// 去掉换行
						$categories = str_replace("\n",'',$categories); 
					}
					// 解析固定链接
					preg_match('/permalink: ([\s\S]+?)[\s\S*?]---/' ,$content,$matches);
					if($matches != NULL) {
						$permalink = $matches[1];
						// 去掉换行
						$permalink = str_replace("\n",'',$permalink); 
					}
					// 解析原文链接
					preg_match('/原文链接=([\s\S]+?)[\s\S*?]作者=/' ,$content,$matches);
					if($matches != NULL) {
						$original_url = $matches[1];
						// 去掉换行
						$original_url = str_replace("\n",'',$original_url); 
					}
					// 解析作者
					preg_match('/作者=([\s\S]+?)[\s\S*?]原文日期=/' ,$content,$matches);
					if($matches != NULL) {
						$author = $matches[1];
						// 去掉换行
						$author = str_replace("\n",'',$author); 
					}
					// 解析原文日期
					preg_match('/原文日期=([\s\S]+?)[\s\S*?]译者=/' ,$content,$matches);
					if($matches != NULL) {
						$orginal_date = $matches[1];
						// 去掉换行
						$orginal_date = str_replace("\n",'',$orginal_date); 
					}
					// 解析译者
					preg_match('/译者=([\s\S]+?)[\s\S*?]校对=/' ,$content,$matches);
					if($matches != NULL) {
						$translator = $matches[1];
						// 去掉换行
						$translator = str_replace("\n",'',$translator); 
					}
					// 解析校对
					preg_match('/校对=([\s\S]+?)[\s\S*?]定稿=/' ,$content,$matches);
					if($matches != NULL) {
						$proofreader = $matches[1];
						// 去掉换行
						$proofreader = str_replace("\n",'',$proofreader); 
					}
					// 解析定稿
					preg_match('/定稿=([\s\S]+?)[\s\S*?]\n/' ,$content,$matches);
					if($matches != NULL) {
						$finalization = $matches[1];
						// 去掉换行
						$finalization = str_replace("\n",'',$finalization); 
					}

					$data = array(
						'title'        => $title,
						'date'         => $date,
						'tags'         => $tags,
						'categories'   => $categories,
						'permalink'    => $permalink,
						'original_url' => $original_url,
						'author'       => $author,
						'orginal_date' => $orginal_date,
						'translator'   => $translator,
						'proofreader'  => $proofreader,
						'finalization' => $finalization
					);
					ToolUtil::p($data);
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