<?php
// //namespace controllers;
// use \Flight;
// use \Controller;

require_once (APPLIB_PATH.'libs/NetUtil.php');
require_once (APPLIB_PATH.'libs/ToolUtil.php');
require_once (APPLIB_PATH.'config/app.inc.php');

/*
 *  用户模块接口
 */
class UserController extends Controller {

    // 用户 Model 操作
    protected $userOpr; 

    public function __construct(){
        // 初始化
        $this->userOpr = Flight::model(INTERFACE_SGUSER);
    }

    // 用户注册
    public function userRegisterV1(){
        // 写log
        $txt = json_encode($_POST);
        $this->writeLog('userRegisterV1：' . $txt);

        // 参数判断
        if(!empty($_POST['userName']) && !empty($_POST['password'])){
            $userName = $_POST['userName'];
            $password = $_POST['password'];

            // 判断是否含有特殊字符
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$userName)){
                $this->errReturn(USERNAME_SPECIAL_CHARACTER,'用户名含有特殊字符');
            }
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$password)){
                $this->errReturn(PASSWORD_SPECIAL_CHARACTER,'密码含有特殊字符');
            }

            // 判断长度
            if(strlen($userName) < USERNAME_LENGTH){
                $this->errReturn(USERNAME_LENGTH_NOT_ENOUGH,'用户名位数必须大于或等于'.USERNAME_LENGTH);
            }
            if(strlen($password) < PASSWORD_LENGTH){
                $this->errReturn(PASSWORD_LENGTH_NOT_ENOUGH,'密码位数必须大于或等于'.PASSWORD_LENGTH);
            }

            // 查询用户名是否存在
            if(!$this->userOpr->isExistByUserName($userName)){
                // 生成盐
                $salt = ToolUtil::randomkeys(6);
                // 封装数据
                $data = array(
                    'account'  => $userName,
                    'password' => md5($password . $salt),
                    'salt'     => $salt,
                    'nickname' => "SwiftGG粉丝:" . $userName,
                    'the_third_type' => 'no',
                    'the_third_keyseri' => 'no',
                    'image_url' => '',
                    'score' => rand(0,100),
                    'created_time' => time(),
                    'updated_time' => time()     
                );
                $userId = $this->userOpr->addUser($data);
                // 返回参数
                $responseData = array('userId' => $userId);
                return $this->sucReturn($responseData);
            }else{
                $this->errReturn(USERNAME_IS_EXIST,'账号已被注册,请重新输入');
            }
        }else{
           $this->errReturn(ERR_PARAMETER,'请求参数有误'); 
        }
    }

    // 用户登录接口
    public function userLoginV1(){
        // 写log
        $txt = json_encode($_POST);
        $this->writeLog('userLoginV1：' . $txt);

        // 参数判断
        if(!empty($_POST['userName']) && !empty($_POST['password'])){
            $userName = $_POST['userName'];
            $password = $_POST['password'];

            // 判断是否含有特殊字符
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$userName)){
                $this->errReturn(USERNAME_SPECIAL_CHARACTER,'用户名含有特殊字符');
            }
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$password)){
                $this->errReturn(PASSWORD_SPECIAL_CHARACTER,'密码含有特殊字符');
            }

            // 判断长度
            if(strlen($userName) < USERNAME_LENGTH){
                $this->errReturn(USERNAME_LENGTH_NOT_ENOUGH,'用户名位数必须大于或等于'.USERNAME_LENGTH);
            }
            if(strlen($password) < PASSWORD_LENGTH){
                $this->errReturn(PASSWORD_LENGTH_NOT_ENOUGH,'密码位数必须大于或等于'.PASSWORD_LENGTH);
            }

            // 查询账号信息
            $userVaild = array(
                'account'  => $userName,
            );
            $userInfo = $this->userOpr->getUserByWhere($userVaild);
            if(!empty($userInfo)){
                // 帐号密码验证
                $userVaild = array(
                    'account'   => $userInfo['account'],
                    'password'  => md5($password . $userInfo['salt'])
                );
                $userInfo = $this->userOpr->getUserByWhere($userVaild);
                if(!empty($userInfo)){
                    // 封装数据
                    $responseData = array('userId' => $userInfo['id']);
                    return $this->sucReturn($responseData);
                }else{
                    $this->errReturn(PASSWORD_IS_ERROR,'密码有误,请重新输入.');
                }
            }else{
                $this->errReturn(USERNAME_IS_ERROR,'账号不存在,请重新输入.');
            }
        }else{
           $this->errReturn(ERR_PARAMETER,'请求参数有误'); 
        }
    }

    // 获取用户信息
    public function getInfoV1(){
        // 写log
        $txt = json_encode($_POST);
        $this->writeLog('getInfoV1：' . $txt);

        // 参数判断
    	if(!empty($_POST['uid'])){
            $uid = $_POST['uid'];

            // 判断是否含有特殊字符
            if(preg_match("/[\'.,。，／:;*?~`!@#$%^&+=＝)(<>{}]|\]|\[|\/|\\\|\"|\|/",$uid)){
                $this->errReturn(UID_SPECIAL_CHARACTER,'含有特殊字符');
            }

            // 查询账号信息
            $userVaild = array(
                'id'  => $uid,
            );
            $userInfo = $this->userOpr->getUserByWhere($userVaild);
            if(!empty($userInfo)){

                $imageUrl = $userInfo['image_url'];
                if($imageUrl == ""){
                    $imageUrl = 'http://i8.tietuku.com/1a055c782b5a4c37.png';
                }

                // 封装数据
                $data = array(
                    'uid' => '' . $uid,
                    'nickname' => $userInfo['nickname'],
                    'imageUrl' => $imageUrl,
                    'score' => $userInfo['score'],
                    'signature' => $userInfo['signature'],
                    'sex' => '' . rand(1,2),
                    'weibo' => $userInfo['weibo'],
                    'github' => $userInfo['github'],
                    'qq' => $userInfo['qq'],
                    'level' => $userInfo['level'],
                    'readArticlesNumber' => '' . rand(0,122),
                    'readWordsNumber' => '' . rand(1000,999999),
                    'collectArticlesNumber' => '' . rand(0,99),
                    'restArticlesNumber' => '' . rand(0,122),
                    'sort' => '' . rand(0,100),
                    'reading' => array(),
                    'collection' => array()
                );
                return $this->sucReturn($data);
            }else{
                $this->errReturn(USERNAME_IS_ERROR, '账号不存在');
            }
        }else{
            $this->errReturn(ERR_PARAMETER, '请求参数有误'); 
        }
    }

}

