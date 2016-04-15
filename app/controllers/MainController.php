<?php
// namespace controllers;
// use \Flight;
// use \Controller;

require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'config/errorCode.inc.php');
require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');

/*
 *  Main模块接口
 */
class MainController extends Controller {

    public function index() {
    	echo 'Hello SwiftGG!';
    }

    public function getAppInfo(){
      // 导入MODEL
    	$articleOpr = Flight::model(INTERFACE_SGARTICLE);
    	$typeOpr    = Flight::model(INTERFACE_SGTYPE);
      $articlesVersion = $articleOpr->getLastCreatedTime();
      $categoriesVersion = $typeOpr->getLastCreatedTime();
      $articleSum = $articleOpr->getSum();
      $data = array(
        "appVersion"        => "0.1.3",
        "categoriesVersion" => $categoriesVersion,
        "articlesVersion"   => $articlesVersion,
        "articlesSum"       => (int)$articleSum,
        "message"           => "Hello, SwiftGG! "
      );
      return $this->sucReturn($data);
    }

}
