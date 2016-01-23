<?php
/**
 * 处理整个项目中的配置文件信息
 */

class Config
{
	/**
	 * 错误编码
	 *
	 * @var int
	 */
	public static $errCode = 0;

	/**
	 * 错误信息
	 *
	 * @var string
	 */
	public static $errMsg = '';

	/**
	 * 保存项目中DB的句柄
	 *
	 * @var array
	 */
	private static $DBResMap = array();

	/**
	 * 保存项目中MemCache的句柄
	 *
	 * @var array
	 */
	private static $CacheResMap = array();

	/**
	 * 保存项目中TTC的句柄
	 *
	 * @var array
	 */
	private static $TTCResMap = array();
	private static $TTC2ResMap = array();
	/**
	 * DB的配置
	 *
	 * @var array
	 */
	private static $DBCfg;

	/**
	 * IP的配置
	 *
	 * @var array
	 */
	private static $IPCfg;
	/**
	 * 保存项目中TTC的配置
	 *
	 * @var array
	 */
	private static $TTCCfg;
	/**
	 * 保存项目中MemCache的配置
	 *
	 * @var array
	 */
	private static $CacheCfg = array();

	/**
	 * 保存来自外部系统的IP Port的配置
	 *
	 * @var array
	 */
	private static $ExtIPCfg = array();


	/**
	 * 初始化配置变量
	 */
	private static function init()
	{
		global $_DB_CFG, $_CACHE_CFG, $_IP_CFG, $_EXT_IP_CFG;

		//var_dump($_DB_CFG);

		// DB 配置
		if (empty(self::$DBCfg)) {
			if(isset($_DB_CFG)){
				self::$DBCfg = &$_DB_CFG;
			} else {
				self::$DBCfg = '';
			}
		}

		if(empty(self::$CacheCfg)){
			if(isset($_CACHE_CFG)){
				self::$CacheCfg = &$_CACHE_CFG;
			} else {
				self::$CacheCfg = '';
			}
		}

		// 内部 ip 配置
		if (empty(self::$IPCfg)) {
			if(isset($_IP_CFG)){
				self::$IPCfg = &$_IP_CFG;
			} else {
				self::$IPCfg = '';
			}
		}

		// 其他 ip 配置
		if (empty(self::$ExtIPCfg)) {
			if(isset($_EXT_IP_CFG)){
				self::$ExtIPCfg = &$_EXT_IP_CFG;
			} else {
				self::$ExtIPCfg = '';
			}
		}
	}

	/**
	 * 清除错误标识，在每个函数调用前调用
	 */
	private static function clearERR()
	{
		self::$errCode = 0;
		self::$errMsg  = '';
	}

	/**
	 * 获得不分 set 的 memcache 对象
	 *
	 * @author	hickwu
	 * @param	key		资源的key
	 * @return	Memcache		memcache 对象, 出错 false
	 */
	public static function getCache($key)
	{
		self::init();
		self::clearERR();

		// 如果在前面已创建该 cache 资源，则直接返回
		if (isset(self::$CacheResMap[$key]))
		{
			return self::$CacheResMap[$key];
		}

		// 判断参数
		if (!isset(self::$CacheCfg[$key]))
		{
			self::$errCode = 20000;
			self::$errMsg = "no cache config info for key {$key}";
			return false;
		}

		// cache 配置
		$cfg = self::$CacheCfg[$key];

		// 自动判断是单节点还是多节点 memcache 连接(一级 key 中有 host)
		$MemCache = new Memcache;
		if (isset($cfg['IP'])) {
			// 单节点连接
			$MemCache->connect($cfg['IP'], $cfg['PORT']);
		} else {
			// 多节点连接
			foreach ($cfg['servers'] as $server){
				$MemCache->addServer($server['IP'], $server['PORT'], 0);
			}
			if ($MemCache === false){
				self::$errCode = 20001;
				self::$errMsg = "add memcache server failed";
				return false;
			}
		}
		// 保存到类属性中
		self::$CacheResMap[$key] = $MemCache;
		return 	self::$CacheResMap[$key];
	}
	/**
	 * 获得 DB 对象
	 *
	 * 由于数据库不同于一般的 server ip/port ，这里不支持 $node 参数指定节点，以免出现不必要的问题。
	 *
	 * @author	hickwu
	 * @param	string	$key		返回 DB 对象
	 * @param	int		$node
	 * @return	Database	DB 对象, 出错 false
	 */
	public static function getDB($key)
	{
		self::init();
		self::clearERR();
		// 如果在前面已创建该 DB 资源，则直接返回
		if (isset(self::$DBResMap[$key])){
			return self::$DBResMap[$key];
		}

		// 判断参数
		if (!isset(self::$DBCfg[$key])){
			self::$errCode = 20000;
			self::$errMsg = "no DB config info for key {$key}";
			return false;
		}
		$cfg = self::$DBCfg[$key];
		// 创建 DB 对象
		$DB = new DB($cfg['IP'], $cfg['PORT'], $cfg['DB'], $cfg['USER'], $cfg['PASSWD']);
		if (empty($DB) || $DB->errCode > 0) {
			self::$errCode = 20001;
			self::$errMsg = "create DB connnect failed for {$key}: " . $DB->errCode . " " . $DB->errMsg;
			return false;
		}
		// 保存到类属性中
		self::$DBResMap[$key] = $DB;
		return self::$DBResMap[$key];
	}
	/**
	 * 获得 ip 和端口等
	 *
	 * @param	string	$key	资源的key
	 * @param	int		$node 	节点数字编号; 如果为 false 表示忽略，返回全部节点
	 * @return 	array	需要的 ip 端口等信息
	 */
	public static function getIP($key, $node = false)
	{
		self::clearERR();
		self::init();
		// 判断参数 key 是否存在
		if (!isset(self::$IPCfg[$key])){
			self::$errCode = 20000;
			self::$errMsg = "no config info for key {$key}";
			return false;
		}

		// 判断是否单节点
		$cfg = self::$IPCfg[$key];
		// 多节点方式
		if ($node === false) {
			// 直接返回
			return $cfg;
		} else {
			// 获得指定(不掩盖错误，不存在则返回错误)
			if (!isset($cfg[$node])) {
				self::$errCode = 20001;
				self::$errMsg = "no node for {$node} in {$key}";
				return false;
			} else {
				return $cfg[$node];
			}
		}
	}

	/**
	 * 获得外部 ip 配置信息
	 *
	 * @param	string	$key	资源的key
	 * @param	int		$node 	节点数字编号; 如果为 false 表示忽略，返回全部节点
	 * @return 	array	需要的 ip 端口等信息
	 */
	public static function getExtIP($key, $node = false)
	{
		self::clearERR();
		self::init();
		// 判断参数 key 是否存在
		if (!isset(self::$ExtIPCfg[$key]))
		{
			self::$errCode = 20000;
			self::$errMsg = "no config info for key {$key}";
			return false;
		}

		// 判断是否单节点
		$cfg = self::$ExtIPCfg[$key];
		// 多节点方式
		if ($node === false) {
			// 直接返回
			return $cfg;
		} else {
			// 获得指定(不掩盖错误，不存在则返回错误)
			if (!isset($cfg[$node])) {
				self::$errCode = 20001;
				self::$errMsg = "no node for {$node} in {$key}";
				return false;
			} else {
				return $cfg[$node];
			}
		}
	}

	/**
	 * 获得 TTC 句柄
	 *
	 * @param	string	$key	资源的key
	 * @return 	TTC	返回TTC句柄
	 */
	public static function getTTC($key)
	{
		self::clearERR();
		self::init();
		// 如果在前面已创建该 ttc 资源，则直接返回
		if (isset(self::$TTCResMap[$key])){
			return self::$TTCResMap[$key];
		}
		// 判断参数
		if (!isset(self::$TTCCfg[$key])){
			self::$errCode = 20000;
			self::$errMsg = "no TTC config info for key {$key}";
			return false;
		}
		// cache 配置
		$cfg = self::$TTCCfg[$key];
		$ttc = new TTC($cfg);
		// 保存到类属性中
		self::$TTCResMap[$key] = $ttc;
		return 	self::$TTCResMap[$key];
	}

	/**
	 * 获得 TTC2 句柄(仅支持批量取的TTC)
	 *
	 * @param	string	$key	资源的key
	 * @return 	TTC2	返回TTC2句柄
	 */
	public static function getTTC2($key)
	{
		self::clearERR();
		self::init();
		// 如果在前面已创建该 ttc 资源，则直接返回
		if (isset(self::$TTC2ResMap[$key])){
			return self::$TTC2ResMap[$key];
		}

		// 判断参数
		if (!isset(self::$TTCCfg[$key])){
			self::$errCode = 20000;
			self::$errMsg = "no TTC config info for key {$key}";
			return false;
		}
		// cache 配置
		$cfg = self::$TTCCfg[$key];
		$ttc = new TTC2($cfg);
		// 保存到类属性中
		self::$TTC2ResMap[$key] = $ttc;
		return 	self::$TTC2ResMap[$key];
	}


	/**
	 * 得到token值
	 *
	 * @param	参数数组
	 * @return 	token值
	 */
	public static function getToken($params)
	{
		$strResouce = "";
		foreach ($params as $key=>$value)
		{
			$strResouce .= $value;
		}

		$strResouce .= WEEDO_MD5_KEY;
		//var_dump($strResouce);
		return md5($strResouce);
	}
}

//End Of Script

?>
