<?php
// //namespace controllers;
// use \Flight;
// use \Controller;

require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'config/app.inc.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');

class UserController extends Controller {

    // 用户 Model 操作
    protected $userOpr; 

    public function __construct(){
        // 初始化
        $this->userOpr = Flight::model(INTERFACE_SGUSER);
    }

    public function otherLoginV1(){
    	echo 'UserController:otherLoginV1';
    }

    public function userRegisterV1(){
    	if(!empty($_POST['userName']) && !empty($_POST['password'])){
            $userName = $_POST['userName'];
            $password = $_POST['password'];
            // 去除注入
            $userName = str_replace("=",'',$userName);
            $password = str_replace("=",'',$password);
            // 查询账号信息
            $userVaild = array(
                'account'  => $userName,
            );
            $userInfo = $this->userOpr->get_one_by_where($userVaild);
            if(empty($userInfo)){
                // 生成盐
                $salt = ToolUtil::randomkeys(6);
                // 封装数据
                $data = array(
                    'account'  => $userName,
                    'password' => md5($password . $salt),
                    'salt'     => $salt,
                    'nickname' => "SwiftGG爱好者:" . $userName,
                    'the_third_type' => 'no',
                    'the_third_keyseri' => 'no',
                    'image_url' => '',
                    'score' => 0,
                    'created_time' => time(),
                    'updated_time' => time()     
                );
                $userId = $this->userOpr->insert($data);
                // 封装数据
                $response = array(
                    'ret'  => 0,
                    'data' => array(
                        'userId' => $userId
                    )
                );
                return $this->ajaxReturn($response);
            }else{
                $this->errReturn(-1,'账号已被注册，请重新输入');
            }
        }else{
           $this->errReturn(-2,'请求有误，参数不能为空'); 
        }
    }

    // 用户登录接口
    public function userLoginV1(){
    	if(!empty($_POST['userName']) && !empty($_POST['password'])){
            $userName = $_POST['userName'];
            $password = $_POST['password'];
            // 去除注入
            $userName = str_replace("=",'',$userName);
            $password = str_replace("=",'',$password);
            // 查询账号信息
            $userVaild = array(
                'account'  => $userName,
            );
            $userInfo = $this->userOpr->get_one_by_where($userVaild);
            if(!empty($userInfo)){
                // 查询账号信息
                $userVaild = array(
                    'account'   => $userInfo['account'],
                    'password'  => md5($password . $userInfo['salt'])
                );
                $userInfo = $this->userOpr->get_one_by_where($userVaild);
                if(!empty($userInfo)){
                    // 封装数据
                    $response = array(
                        'ret'  => 0,
                        'data' => array(
                            'userId' => $userInfo['id']
                        )
                    );
                    return $this->ajaxReturn($response);
                }else{
                    $this->errReturn(-1,'密码错误');
                }
            }else{
                $this->errReturn(-1,'账号不存在');
            }
        }else{
           $this->errReturn(-2,'请求有误，参数不能为空'); 
        }
    }

    public function getInfoV1(){
    	if(!empty($_POST['uid'])){
            $uid = $_POST['uid'];
            // 去除注入
            $uid = str_replace("=",'',$uid);
            // 查询账号信息
            $userVaild = array(
                'id'  => $uid,
            );
            $userInfo = $this->userOpr->get_one_by_where($userVaild);
            if(!empty($userInfo)){

                $imageUrl = $userInfo['image_url'];
                if($imageUrl == ""){
                    $imageUrl = 'http://i8.tietuku.com/1a055c782b5a4c37.png';
                }

                // 封装数据
                $data = array(
                    'uid' => $uid,
                    'nickname' => $userInfo['nickname'],
                    'imageUrl' => $imageUrl,
                    'score' => $userInfo['score'],
                    'signature' => $userInfo['signature'],
                    'sex' => rand(1,2),
                    'weibo' => $userInfo['weibo'],
                    'github' => $userInfo['github'],
                    'qq' => $userInfo['qq'],
                    'level' => $userInfo['level'],
                    'readArticlesNumber' => rand(0,122),
                    'readWordsNumber' => rand(1000,999999),
                    'collectArticlesNumber' => rand(0,99),
                    'restArticlesNumber' => rand(0,122),
                    'sort' => rand(0,100),
                    'reading' => array(),
                    'collection' => array()
                );
                $response = array(
                    'ret'  => 0,
                    'data' => $data
                );
                return $this->ajaxReturn($response);
            }else{
                $this->errReturn(-1,'账号不存在');
            }
        }else{
            $this->errReturn(-2,'请求有误'); 
        }
    }

    // 错误返回接口
    public function errReturn($ret, $errMsg){
        // 封装数据
        $response = array(
            'ret'    => $ret,
            'errMsg' => $errMsg,
        );
        return $this->ajaxReturn($response);
    }

}

