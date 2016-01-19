<?php

require_once (WEEDOLIB_PATH."config/module.inc.php");
require_once (WEEDOLIB_PATH.'config/errorCode.inc.php');
require_once (WEEDOLIB_PATH.'libs/ILog.php');

class moduleTool
{
	public static $errCode = 0;	
	public static $errMsg = 0;
	public static $restart = 1;

	static function checkPostParameter($functionName)
	{
		$functionModAct = split('_', $functionName);	
		$functionPars[] = "ver";
		$functionPars[] = "appId";
		$functionPars[] = "appVer";
		$functionPars[] = "lang";
		$functionPars[] = "tk";
		$functionPars[] = "ts"; //时间戳
		$functionPars[] = "isSaveResponse";
		$functionPars[] = "mod";
		$functionPars[] = "act";
		global $postParameter;

		if (isset($postParameter['isSaveResponse']) && $postParameter['isSaveResponse'] == true) {
            $postParameter['isSaveResponse'] = 1;
        }
        elseif (isset($postParameter['isSaveResponse']) && $postParameter['isSaveResponse'] == false) {
        	$postParameter['isSaveResponse'] = 0;
        }

		if(!isset(moduleInc::$functionParameterArray[$functionName."Parameter"]))
		{
			self::$errCode = GAME_ERR_FUNCTIONPARAMETER;
			self::$errMsg = "can not find function parameter";
			self::$restart = 1;
			return false;
		}
		$functionPars = array_merge($functionPars, moduleInc::$functionParameterArray[$functionName."Parameter"]);
		foreach ($functionPars as $functionPar)
		{
			if(!isset($postParameter[$functionPar]))
			{
				self::$errCode = GAME_ERR_PARAMETER;
			    self::$errMsg = "lack of parameter, $functionPar";
			    self::$restart = 1;
				return false;
			}
			if($functionPar != "tk" && $functionPar != "userId")
			{
				$par[$functionPar] = $postParameter[$functionPar];
				$keys[] = $functionPar;		
			}

			if ($functionPar == 'ts') {
				$timeStamp = $postParameter['ts'];
			}
		}
		//accessToken参数兼容
		if (isset($postParameter['accessToken'])) {
			$par['accessToken'] = $postParameter['accessToken'];
			$keys[] = 'accessToken';		
		}
		//commonLogin参数兼容
		if ($postParameter['act'] == 'commonLogin') {
			$par['userId'] = $postParameter['userId'];
			$keys[] = 'userId';
		}
		//setGuideData参数兼容
		if ($postParameter['act'] == 'setGuideData') {
			if (isset($postParameter['triggerCount'])) {
				$par['triggerCount'] = $postParameter['triggerCount'];
				$keys[] = 'triggerCount';
			}
			if (isset($postParameter['completeCount'])) {
				$par['completeCount'] = $postParameter['completeCount'];
				$keys[] = 'completeCount';
			}
			if (isset($postParameter['step'])) {
				$par['step'] = $postParameter['step'];
				$keys[] = 'step';
			}
		}

		$clientToken = $postParameter['tk'];

		//检查请求参数中的时间戳是否已经超过五分钟
		if (time() - $timeStamp >= 300) {
			self::$errCode = REQUEST_OVERTIME;
			self::$errMsg = "request overtime";
			self::$restart = 1;
			return true;
		}

		sort($keys, SORT_STRING);
		//var_dump($keys);
		//exit();
		foreach ($keys as $k => $v) {
			$temp[] = $par[$v];
		}
		$svrToken = Config::getToken($temp);
		//ILog::writeTimeLog($par);
		//exit();
		if ($clientToken != $svrToken) 
		{
			self::$errCode = GAME_ERR_ENCRYPTION;
			self::$errMsg = "token array";
			self::$restart = 1;
			return false;
		}
		else
		{
			return true;
		}

	}
 
}


?>