<?php
//宏定义

// local
// define('APPLIB_PATH', $_SERVER['DOCUMENT_ROOT'] . "/SwiftGGAppServer/app/");
// define('LOG_ROOT', $_SERVER['DOCUMENT_ROOT'] . "/SwiftGGAppServer/" );
define('ARTICLE_PATH', "/Applications/XAMPP/xamppfiles/htdocs/source/_posts/");

// server
define('APPLIB_PATH', $_SERVER['DOCUMENT_ROOT'] . "/app/");
define('LOG_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
//define('ARTICLE_PATH', "/source/_posts/");

//路径：  /usr/share/nginx/html/SwiftGGAppServer/

//数据库以及路由配置
return array(
    "flight.controllers.path" => dirname(__DIR__)."/controllers",
    "flight.models.path" => dirname(__DIR__)."/models",
    //"flight.views.path" => dirname(__DIR__)."/views",
    "flight.libs.path" => dirname(__DIR__)."/libs",
    // "flight.uploads.path" =>

    //路由配置
    "flight.routes" => array(
        // V1.0接口规范
        array("GET /v1/app/info" , "Main:getAppInfo"),
        array("POST /v1/user/otherLogin" , "User:otherLoginV1"),
        array("POST /v1/user/userRegister", "User:userRegisterV1"),
        array("POST /v1/user/userLogin" , "User:userLoginV1"),
        array("GET /v1/user/info" , "User:getInfoV1"),
        array("GET /v1/article/categoryList" , "Article:getCategoryListV1"),
        array("GET /v1/article" , "Article:getArticlesByCategoryV1"),
        array("GET /v1/article/detail" , "Article:getDetailV1"),
        array("/v1/catch/newArticle" , "Catch:addNewArticle"),
        // 其它规则
        array("*", "Main:index"),
    ),

    //mysql配置
    "db.host" => "localhost",
    "db.port" => 3306,
    "db.user" => "root",
    "db.pass" => "",
    "db.name" => "swiftggapp",
    "db.charset" => "utf8mb4",
    //"db.charset" => "utf8mb4_unicode_ci",

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
