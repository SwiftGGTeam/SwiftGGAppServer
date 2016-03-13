<?php
/**
 * ILog.php
 *
 * @date          	2014-1-2	
 */

require_once (WEEDOLIB_PATH . 'libs/Logger.php');
require_once (WEEDOLIB_PATH . 'config/errorCode.inc.php');

define("REMOTE_LOG_SVR_HOST", "");
define("REMOTE_LOG_SVR_PORT", "80");
class ILog
{
	public $errCode = 0;
	public $errMsg = '';

	private function _clearERR()
	{
		$this->errCode = 0;
		$this->errMsg = '';
	}

	public static function writeErrLog($errCode, $arrParam = array(), $restart = 1)
 	{
 		global $postParameter;
 		$ver 	= $postParameter['ver'];
		$appId 	= $postParameter['appId'];
		$appVer = $postParameter['appVer'];
		$lang 	= $postParameter['lang'];

		$logMsg = sprintf("[ver=%s][appId=%s][appVer=%s][lang=%s][errCode=%s]",
							$ver,
							$appId,
							$appVer,
							$lang,
							$errCode
							//$arrErrMsg[$errCode]
							//errMsgInc::$arrErrMsg[$errCode]
							);
		foreach ($arrParam as $key => $value)
		{
			$logMsg .= "[$key=$value]";
		}

		Logger::err($logMsg, GAME_ERR_LOG_KEY);

		//数据库错误和内部接口找不到，不需要restart
		if ($errCode == GAME_ERR_DB_EXEC || $errCode == GAME_ERR_INTERFACE_NOT_FOUND) {
			$restart = 0;
		}

		//return array("ret" => $errCode, "errMsg" => errMsgInc::$arrErrMsg[$errCode]);
		return array("ret" => $errCode, "restart" => $restart);
	}

	public static function writeInfoLog($arrParam = array())
 	{
 		global $postParameter;
 		$ver 	= $postParameter['ver'];
		$appId 	= $postParameter['appId'];
		$appVer = $postParameter['appVer'];
		$lang 	= $postParameter['lang'];
 		$logMsg = sprintf("[ver=%s][appId=%s][appVer=%s][lang=%s]",
							$ver,
							$appId,
							$appVer,
							$lang);
		foreach ($arrParam as $key => $value)
		{
			$logMsg .= "[$key=$value]";
		}

		Logger::err($logMsg, GAME_INFO_LOG_KEY);
	}

	public static function writeTimeLog($arrParam = array())
 	{
// 		$ver 	= $_POST['ver'];
//		$appId 	= $_POST['appId'];
//		$appVer = $_POST['appVer'];
//		$lang 	= $_POST['lang'];
// 		$logMsg = sprintf("[ver=%s][appId=%s][appVer=%s][lang=%s]",
//							$ver,
//							$appId,
//							$appVer,
//							$lang);
 		if (RECORD_LOGIC_TIME == 0) {
			return;
		}
		$logMsg = "";
		foreach ($arrParam as $key => $value)
		{
			$logMsg .= "[$key=$value]";
		}

		Logger::warn($logMsg, GAME_TIME_LOG_KEY);
	}


	public static function writeRemoteLog($data = array())
	{
		//global $postParameter;
		if (UPLOAD_STAT_DATA == 0) {
			return;
		}
		$iosMess = self::getIosMessage();
		$iosMess['time'] = date("Y-m-d H:i:s");

		$postData = array_merge($iosMess, $data);

		$post = http_build_query($postData);
		$len = strlen($post);

		$fp = fsockopen(REMOTE_LOG_SVR_HOST, REMOTE_LOG_SVR_PORT, $errno, $errstr, 30);

		$path = "/stat/upload/uploadData.php";
		if (!$fp)
		{
			echo "$errstr ($errno)\n";
			return;
		}
    	$out = "POST $path HTTP/1.1\r\n";
    	$out .= "Host: REMOTE_LOG_SVR_HOST\r\n";
    	$out .= "Content-type: application/x-www-form-urlencoded\r\n";
    	$out .= "Connection: Close\r\n";
    	$out .= "Content-Length: $len\r\n";
    	$out .= "\r\n";
    	$out .= $post."\r\n";
    	fwrite($fp, $out);

    // get response
                //$response = '';
                //while($row=fread($fp, 4096)){
        //$response .= $row;
    //            }¢

	}

	//获取ios版本等等信息
	private function getIosMessage(){
		$iosMess=array();
		$pattern = "/([\w\W]+?) \(([\w\W]+?); ([\w\W]+?); ([\w\W]+?)\)/i";
   		preg_match_all($pattern, $_SERVER["HTTP_USER_AGENT"], $matches);
   		//var_dump($matches);
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$iosMess['ipAddr'] = trim($_SERVER['HTTP_X_FORWARDED_FOR']);
		}
		else
		{
			$iosMess['ipAddr']	= str_pad( (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0'), 15 );
		}
   		if(count($matches) == 5)//能解析到
   		{
   			$iosMess['appVersion'] 	= empty($matches[1][0])?"":$matches[1][0];
   			$iosMess['device']		= empty($matches[2][0])?"":$matches[2][0];
   			$iosMess['iosVersion']	= empty($matches[3][0])?"":$matches[3][0];
   			$iosMess['lang']		= empty($matches[4][0])?"":$matches[4][0];
   		}
   		else
   		{
   			$iosMess['appVersion'] 	= '';
   			$iosMess['device']		= '';
   			$iosMess['iosVersion']	= '';
   			$iosMess['lang']		= '';
   		}

   		return $iosMess;
	}

}
?>
