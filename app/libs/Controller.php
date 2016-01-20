<?php
class Controller {

    protected static $_dbInstances = array();
    protected static $_cacheInstances = array();
    protected static $_logInstances = array();
    protected static $_controllerInstances = array();
    protected static $_modelInstances = array();
    protected static $_routes = array();

    protected static $_mongoClient = null;

    public static function init() {
        date_default_timezone_set("Etc/GMT");
        if(get_magic_quotes_gpc()) {
            $_GET = self::stripslashesDeep($_GET);
            $_POST = self::stripslashesDeep($_POST);
            $_COOKIE = self::stripslashesDeep($_COOKIE);
        }
        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        //Flight::map("db", array(__CLASS__, "db"));
        //Flight::map("cache", array(__CLASS__, "cache"));
        Flight::map("log", array(__CLASS__, "log"));
        Flight::map("curl", array(__CLASS__, "curl"));
        Flight::map("halt", array(__CLASS__, "halt"));
        Flight::map("getRunTime", array(__CLASS__, "getRunTime"));
        Flight::map("returnJson", array(__CLASS__, "returnJson"));
        Flight::map("controller", array(__CLASS__, "getController"));
        Flight::map("model", array(__CLASS__, "getModel"));
        Flight::map("url", array(__CLASS__, "url"));

        Flight::map("connectMongoDB", array(__CLASS__, "connectMongoDB"));
        Flight::map("closeMongoDB", array(__CLASS__, "closeMongoDB"));
        Flight::map("connectRedis", array(__CLASS__, "connectRedis"));

        Flight::set('flight.log_errors', true);

        //写一个日志
        //if(Flight::request()->method == "POST") {
           // Flight::log("post-".date("Ymd"))->info(print_r($_POST, TRUE));
        //}

        self::initRoute();
    }

    public static function stripslashesDeep($data) {
        if(is_array($data)) return array_map(array(__CLASS__, __FUNCTION__), $data);
        else return stripslashes($data);
    }
    
    //连接mysql，已经不再需要，后面要去掉
    /*public static function db($name = "db") {
        if(!isset(self::$_dbInstances[$name])) {
            $db_host = Flight::get("$name.host");
            $db_port = Flight::get("$name.port");
            $db_user = Flight::get("$name.user");
            $db_pass = Flight::get("$name.pass");
            $db_name = Flight::get("$name.name");
            $db_charset = Flight::get("$name.charset");

            if(is_null($db_host)) {
                $db_host = "localhost";
            }

            if(is_null($db_port)) {
                $db_port = 3306;
            }

            if(is_null($db_user)) {
                $db_user = "";
            }

            if(is_null($db_pass)) {
                $db_pass = "";
            }

            if(is_null($db_name)) {
                $db_name = "";
            }

            if(is_null($db_charset)) {
                $db_charset = "utf8";
            }

            $db = new medoo(array(
                "database_type" => "mysql",
                "database_name" => $db_name,
                "server" => $db_host,
                "port" => $db_port,
                "username" => $db_user,
                "password" => $db_pass,
                "charset" => $db_charset
            ));

            self::$_dbInstances[$name] = $db;
        }

        return self::$_dbInstances[$name];
    }*/

    /*public static function cache($path = "data") {
        $path = Flight::get("cache.path")."/$path";
        if(!is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }
        if(!isset(self::$_cacheInstances[$path])) {
            $cache = new \Doctrine\Common\Cache\FilesystemCache($path, ".cache");
            self::$_cacheInstances[$path] = $cache;
        }

        return self::$_cacheInstances[$path];
    }*/

    public static function log($name = "app") {
        $file = Flight::get("log.path")."/$name.log";
        $path = dirname($file);
        if(!is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }
        if(!isset(self::$_logInstances[$name])) {
            $logger = new \Apix\Log\Logger\File($file);
            self::$_logInstances[$file] = $logger;
        }

        return self::$_logInstances[$file];
    }

    public static function curl() {
        $curl = new \Curl\Curl();
        $curl->setOpt(CURLOPT_TIMEOUT, 10);
        return $curl;
    }

    public static function halt($msg = "", $code = 200) {
        Flight::response(false)
            ->status($code)
            ->header("Content-Type", "text/html; charset=utf8")
            ->write($msg)
            ->send();
    }

    public static function getRunTime() {
        if(!defined("START_TIME")) {
            return "";
        }

        $stime = explode(" ", START_TIME);
        $etime = explode(" ", microtime());
        return sprintf("%0.4f", round($etime[0]+$etime[1]-$stime[0]-$stime[1], 4));
    }

    public static function returnJson($status, $msg, $data = NULL, $is_return = false) {
        $res = array(
            "status" => $status,
            "msg" => $msg,
            "data" => $data
        );
        if($is_return) {
            return $res;
        } else {
            Flight::json($res);
        }
    }

    public static function getController($name) {
        $class = "\\".trim(str_replace("/", "\\", $name), "\\")."Controller";
        if(!isset(self::$_controllerInstances[$class])) {
            $instance = new $class();
            self::$_controllerInstances[$class] = $instance;
        }

        return self::$_controllerInstances[$class];
    }

    public static function getModel($name, $initDb = TRUE) {
        $class = "\\".trim(str_replace("/", "\\", $name), "\\")."Model";
        if(!isset(self::$_modelInstances[$class])) {
            $instance = new $class();
            //if($initDb) {
                //$instance->setDb(self::db());
            //}
            self::$_modelInstances[$class] = $instance;
        }

        return self::$_modelInstances[$class];
    }

    public static function initRoute() {
        $routes = Flight::get("flight.routes");
        if(is_array($routes)) {
            foreach($routes as $route) {
                self::$_routes[$route[1]] = $route[0];
                $tmp = explode(":", $route[1]);
                $class = "\\".trim(str_replace("/", "\\", $tmp[0]), "\\")."Controller";
                $func = "@".$tmp[1];
                $pattern = $route[0];

                //echo "class:$class func:$func pattern:$pattern";die;
                Flight::route($pattern, array($class, $func));
            }
        }
    }

    public static function url($name, array $params = array()) {
        if(!isset(self::$_routes[$name])) {
            return "/";
        } else {
            $url = self::$_routes[$name];
            foreach($params as $k => $v) {
                if(preg_match("/^\w+$/", $v)) {
                    $url = preg_replace("#@($k)(:([^/\(\)]*))?#", $v, $url);
                }
            }
            return $url;
        }
    }

    public static function __callStatic($name, $arguments) {
        foreach($arguments as $k => $v) {
            Flight::request()->query[$k] = $v;
        }

        $class = get_called_class();
        $controller = new $class();
        $name = substr($name, 1);
        $controller->$name();
    }

    //连接mongoDB数据库,直接返回已经选择了frontiers数据库的对象
    public static function connectMongoDB() {
        
        $mongo_server = Flight::get("mongo.server");
        $mongo_username = Flight::get("mongo.username");
        $mongo_password = Flight::get("mongo.password");
        $mongo_database = Flight::get("mongo.database");
        
        $mongoConnectStr = sprintf("mongodb://%s:%s@%s",$mongo_username, $mongo_password, $mongo_server);
        self::$_mongoClient = new MongoClient($mongoConnectStr);
        $gameDB = self::$_mongoClient -> $mongo_database;
        
        return $gameDB;
    }
    //关闭数据库连接
    public static function closeMongoDB()
    {
        $collection = self::$_mongoClient -> getConnections();
        if (is_array($collection)&&!empty($collection)) {
            self::$_mongoClient -> close($collection[0]['hash']);
        }
    }

    //连接redis,返回一个redis连接
    public static function connectRedis() {
        $redis_server = Flight::get("redis.server");
        $redis_port = Flight::get("redis.port");

        $redisInstance = new Redis();
        $redisInstance->pconnect($redis_server, $redis_port);

        return $redisInstance;
    }
}

