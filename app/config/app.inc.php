<?php
/**
 * app.inc.php -- app相关配置
 */
//定义每个Interface的名称
define("INTERFACE_SGARTICLE" , "SGArticle");
define("INTERFACE_SGTYPE" , "SGType");
define("INTERFACE_SGUSER" , "SGUser");

//定义数据库表名称
define('DB_TABLE_ARTICLE' , "sg_article");
define('DB_TABLE_ARTICLE_TYPE' , "sg_article_type");
define('DB_TABLE_TYPE' , "sg_type");
define('DB_TABLE_USER' , "sg_user");

//用户模块
define('USERNAME_LENGTH', 3);
define('PASSWORD_LENGTH', 3);

?>