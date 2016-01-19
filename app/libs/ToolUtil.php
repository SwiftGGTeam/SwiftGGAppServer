<?php

class ToolUtil
{
	//获取当前Unix时间戳和微秒数
    public static function getMicrotime()
	{
   		list($usec, $sec) = explode(" ", microtime());
   		return ((float)$usec + (float)$sec);
	}
}


?>