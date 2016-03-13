<?php

//系统和公共错误
define("ERR_PARAMETER", 			    -1001); //参数错误
define("GAME_ERR_DB_EXEC", 				-1002); //数据库执行失败
define("GAME_ERR_DUPLICATED_REQUEST",   -1003); //重复提交请求
define("GAME_ERR_ENCRYPTION",         	-1004); //服务器验证tk失败
define("GAME_ERR_COMPRESSION",          -1005); //返回数据压缩失败
define("GAME_ERR_INTERFACE_NOT_FOUND",  -1006); //内部接口不存在
define("REQUEST_OVERTIME",              -1007); //请求超时
define("RESPONSE_IS_NOT_EXIST",         -1008); //tk对应的函数返回值不存在
define("USERID_IS_NOT_RIGHT",           -1009); //tk对应的函数返回值的userId不正确
define("GAME_ERR_CONFIG_WRONG",         -1010); //配置不一致错误
define("SERVER_MAINTENANCE",            -1011); //服务器维护中
//用户模块
define("NICKNAME_IS_EXIST",             -1101); //注册时，nickName已经存在
define("USER_NOT_FOUND",		        -1102); //找不到的用户
define("DUPLICATED_USER",		        -1103); //GC绑定用户时，出现2条记录
define("REFRESHTOKEN_IS_NOT_EXIST",     -1104); //根据refreshToken生成新的accessToken时，没有找到refreshToken相关数据
define("USERNAME_IS_EXIST",             -1105); //注册时，用户名已经存在
define("USERNAME_SPECIAL_CHARACTER",    -1106); //帐号含有特殊字符串
define("PASSWORD_SPECIAL_CHARACTER",    -1107); //密码含有特殊字符串
define("USERNAME_LENGTH_NOT_ENOUGH",    -1108); //帐号长度不足
define("PASSWORD_LENGTH_NOT_ENOUGH",    -1109); //密码长度不足
define("USERNAME_IS_ERROR",             -1110); //帐号有误
define("PASSWORD_IS_ERROR",             -1111); //密码有误
define("UID_SPECIAL_CHARACTER",         -1112); //uid含有特殊字符串
//文章模块
define("TYPES_IS_NULL",                 -1201); //注册时，nickName已经存在
define('TYPE_IS_NOT_EXIST',             -1202); 
define("CATEGORYID_SPECIAL_CHARACTER",  -1203); //categoryid含有特殊字符串
?>