<?php
//宏定义
define('SWIFTGGAPPLIB_PATH', "/SwiftGGAppServer/app/");
define('LOG_ROOT', "/SwiftGGAppServer/app/logs/");

//路径：  /usr/share/nginx/html/SwiftGGAppServer/

//数据库以及路由配置
return array(
    "flight.controllers.path" => dirname(__DIR__)."/controllers",
    "flight.models.path" => dirname(__DIR__)."/models",
    //"flight.views.path" => dirname(__DIR__)."/views",
    "flight.libs.path" => dirname(__DIR__)."/libs",

    //路由配置
    "flight.routes" => array(
        array("/v1", "Main:index"),
        array("/receiveResourceFile", "Main:receiveResourceFile"),
    ),
    
    //mysql配置
    "db.host" => "localhost",
    "db.port" => 3306,
    "db.user" => "",
    "db.pass" => "",
    "db.name" => "",
    "db.charset" => "utf8",
    
    //"cache.path" => dirname(__DIR__)."/storage/cache",
    "log.path" => dirname(__DIR__)."/logs",

    //mongoDB配置
    "mongo.server" => "",
    "mongo.username" => "",
    "mongo.password" => "",
    "mongo.database" => '',

    //redis配置
    "redis.server" => "localhost",
    "redis.port" => 6380,

);


