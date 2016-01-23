<?php
/**
 * 封装一些常用的网络操作函数
 * @author 付学宝
 */
abstract class NetUtil
{
	/**
	 * 错误编码
	 */
	public static $errCode = 0;
	/**
	 * 错误信息,无错误为''
	 */
	public static $errMsg  = '';

	/**
	 * 清除错误信息,在每个函数的开始调用
	 */
	private static function clearError()
	{
		self::$errCode = 0;
		self::$errMsg	= '';
	}

	/**
	 * 对socket_read的封装,支持多个包的传播,此函数针对TcpServer的
	 * 前8个字节为消息的长度
	 * 接下来的4个字节为错误编码
	 * 接下来的是正文
	 * @param socket      socket句柄
	 * @param int maxLength   能接收数据的字符串长度
	 *
	 * @return string 正确返回读取的数据,错误返回false
	 */
	public static function tcpSocketRead(&$socket, $maxLength)
	{
		self::clearError();
		$str = @socket_read($socket, 10240);
		if ($str === false){
			self::$errCode = 10102;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			return false;
		}
		if (strlen($str) < 8) {
			self::$errCode = 10102;
			self::$errMsg  = 'bad tcp bag';
			return false;
		}
		$len = trim(substr($str, 0, 8));
		if(!is_numeric($len)){
			self::$errCode = 10104;
			self::$errMsg  = 'bad tcp bag';
			return false;
		}
		if ($len > $maxLength) {
			self::$errCode = 10105;
			self::$errMsg  = 'tcp bag too big';
			return false;
		}
		$message = substr($str, 8);
		$n = strlen($message);
		if ($n == $len) {
			return $message;
		}
		if ($n > $len) {
			self::$errCode = 10104;
			self::$errMsg  = 'bad tcp bag';
			return false;
		}

		while ($len > $n)
		{
			$tmp = @socket_read($socket, 10240);
			if ($tmp === false) {
				self::$errCode = 10102;
				self::$errMsg  = @socket_strerror(@socket_last_error($socket));
				return false;
			}
			$message .= $tmp;
			$n = strlen($message);
			unset($tmp);
		}
		if ($n != $len) {
			self::$errCode = 10105;
			self::$errMsg  = 'bad tcp bag';
			return false;
		}
		return $message;
	}

	/**
	 * 对socket_write的封装,支持多个包的传播,此函数针对TcpServer的
	 *
	 * @param socket    socket句柄
	 * @param string message    需要发送的消息
	 *
	 * @return bool 正确返回true,错误返回false
	 */
	public static function tcpSocketWrite(&$socket, $message)
	{
		self::clearError();
		$len     = strlen($message);
		$padStr  = str_pad($len, 8, ' ', STR_PAD_RIGHT);
		$message = $padStr.$message;
		$len = $len + 8;
		$n = @socket_write($socket, $message, $len);
		if($n === false) {
			self::$errCode = 10103;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			return false;
		}
		while ($n < $len) {
			$tmp   = substr($message, $n);
			$tmp_n = @socket_write($socket, $tmp, ($len - $n));
			if ($tmp_n === false) {
				self::$errCode = 10103;
				self::$errMsg  = @socket_strerror(@socket_last_error($socket));
				return false;
			}
			$n += $tmp_n;
		}
		if ($n == $len) {
			return true;
		}
		return false;
	}

	/**
	 * 处理简单的tcp发包收包,只适合短包的操作,最大10k
	 * 实际上受MTU[Maximum Transmission Unit]限制, 大部分网络设备的MTU都是1500, 故每个包不会超过1500 bytes
	 *
	 * @param string ip    	IP地址
	 * @param int port  	端口
	 * @param string cmd   	向server发送命令
	 * @param int n     	错误重试次数
	 * @param int timeout_sec      超时秒
	 * @param int timeout_usec     超时u秒
	 *
	 * @return string 错误返回false,正确返回收到的信息
	 */
	public static function tcpCmd($ip, $port, $cmd, $n = 2, $timeout_sec = 2, $timeout_usec = 0)
	{
		self::clearError();
		$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!$socket) {
			self::$errCode = 10101;
			self::$errMsg  = @socket_strerror(@socket_last_error());
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$timeout_sec, "usec"=>$timeout_usec))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout_sec, "usec"=>$timeout_usec))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		$ret = false;
		for ($i = 0; $i < $n; $i++){
			$ret = @socket_connect($socket, $ip, $port);
			if ($ret == true) break;
		}
		if ($ret === false) {
			self::$errCode = 10107;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		$len = strlen($cmd);
		$n = 0;
		$tmp = $cmd;
		while ($n < $len){
			$ret = @socket_write($socket, $tmp, $len);
			if ($ret == false) {
				self::$errCode = 10103;
				self::$errMsg  = @socket_strerror(@socket_last_error($socket));
				@socket_close($socket);
				return false;
			}
			$n += $ret;
			if ($n < $len) {
				$tmp = substr($tmp, $ret);
			}
		}
		$rev = @socket_read($socket, 10240);
		if ($rev == false) {
			self::$errCode = 10102;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
		}

		@socket_close($socket);
		return $rev;
	}

	/**
	 * 处理简单的tcp发包收包,只适合短包的操作,最大10k
	 * 实际上受MTU[Maximum Transmission Unit]限制, 大部分网络设备的MTU都是1500, 故每个包不会超过1500 bytes
	 *
	 * @param string ip    	IP地址
	 * @param int port  	端口
	 * @param string cmd   	向server发送命令
	 * @param int n     	错误重试次数
	 * @param int timeout_sec      超时秒
	 * @param int timeout_usec     超时u秒
	 *
	 * @return string 错误返回false,正确返回收到的信息
	 */
	public static function tcpLongCmd($ip, $port, $cmd, $n = 2, $timeout_sec = 2, $timeout_usec = 0)
	{
		self::clearError();
		$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!$socket) {
			self::$errCode = 10101;
			self::$errMsg  = @socket_strerror(@socket_last_error());
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$timeout_sec, "usec"=>$timeout_usec))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout_sec, "usec"=>$timeout_usec))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		$ret = false;
		for ($i = 0; $i < $n; $i++){
			$ret = @socket_connect($socket, $ip, $port);
			if ($ret == true) break;
		}
		if ($ret === false) {
			self::$errCode = 10107;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		$len = strlen($cmd);
		$n = 0;
		$tmp = $cmd;
		while ($n < $len){
			$ret = @socket_write($socket, $tmp, $len);
			if ($ret == false) {
				self::$errCode = 10103;
				self::$errMsg  = @socket_strerror(@socket_last_error($socket));
				@socket_close($socket);
				return false;
			}
			$n += $ret;
			if ($n < $len) {
				$tmp = substr($tmp, $ret);
			}
		}

		$tmp = true;
		$rev = "";
		while ( !empty( $tmp ) )
		{
			$tmp = socket_read($socket, 10240);
			$rev = $rev.$tmp;
            /*
			if($tmp[strlen($tmp) - 3] == "\r" && $tmp[strlen($tmp) - 2] == "\n")
			{
				break;
			}
            */
		}
		@socket_close($socket);
		return $rev;
	}


	/**
	 * 对socket_recvfrom的封装,支持对包的校验,此函数针对udpServer的
	 *
	 * @param socket    socket句柄
	 * @param string message    需要发送的消息
	 * @param string ip ip地址
	 * @param int port  端口
	 *
	 * @return bool 正确返回true,错误返回false
	 */
	public static function udpSocketRecvFrom(&$socket, $maxLength, &$ip, &$port)
	{
		self::clearError();
		$n = @socket_recvfrom($socket, $message, $maxLength, 0, $ip, $port);
		if ($n === false){
			self::$errCode = 10104;
			self::$errMsg  = @socket_strerror(@socket_last_error());
			return false;
		}
		if (strlen($message) < 8 || $n < 8) {
			self::$errCode = 10104;
			self::$errMsg  = 'bad udp bag';
			return false;
		}
		$len = trim(substr($message, 0, 8));
		if(!is_numeric($len))
		{
			self::$errCode = 10104;
			self::$errMsg  = 'bad udp bag';
			return false;
		}
		if ($len > $maxLength) {
			self::$errCode = 10105;
			self::$errMsg  = 'udp bag too big';
			return false;
		}
		$message = substr($message, 8);
		$n = strlen($message);
		if ($n == $len) {
			return $message;
		}
		self::$errCode = 10104;
		self::$errMsg  = 'bad udp bag';
		return false;
	}

	/**
	 * 对socket_sendto的封装,支持对包的校验,此函数针对udpServer的,加8个字符的长度
	 *
	 * @param socket    socket句柄
	 * @param string message    需要发送的消息
	 * @param string ip ip地址
	 * @param int port  端口
	 *
	 * @return bool 正确返回true,错误返回false
	 */
	public static function udpSocketSendTo(&$socket, $message, $ip, $port)
	{
		self::clearError();
		$len = strlen($message);
		$padStr  = str_pad($len, 8, ' ', STR_PAD_RIGHT);
		$message = $padStr.$message;
		$len += 8;
		$n = @socket_sendto($socket, $message, $len, 0, $ip, $port);
		if ($n === $len) {
			self::$errCode = 10103;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			return true;
		}
		self::$errCode = 10103;
		self::$errMsg ='udp send error';
		return false;
	}
	/**
	 * 正确返回接受到的数据,错误返回false
	 *
	 * @param string ip    ip地址
	 * @param int port    端口
	 * @param string cmd    命令字符串
	 * @param boolean isResponse	是否需要回复
	 * @param int timeout    超时时间
	 */
	public static function udpCmd($ip, $port, $cmd, $isResponse=true, $timeout = 2, $utmo = 0)
	{
		self::clearError();
		$socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if (!$socket) {
			self::$errCode = 10101;
			self::$errMsg = @socket_strerror(@socket_last_error());
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$timeout, "usec"=>$utmo))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout, "usec"=>$utmo))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		$n = @socket_sendto($socket, $cmd, 10240, 0, $ip, $port);
		if ($n == -1) {
			self::$errCode = 10103;
			self::$errMsg = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		if ($isResponse === false) {
			return true;
		}
		$ret = @socket_recvfrom($socket, $revBuf, 10240, 0, $ip, $port);
		if ($ret == -1) {
			self::$errCode = 10102;
			self::$errMsg = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		return $revBuf;
	}

	/**
	 * 正确返回接受到的数据,错误返回false
	 *
	 * @param string ip    ip地址
	 * @param int port    端口
	 * @param string cmd    命令字符串
	 * @param boolean isResponse	是否需要回复
	 * @param int timeout    超时时间
	 */
	public static function udpPHPCmd($ip, $port, $cmd, $isResponse=true, $timeout = 2, $utmo = 0)
	{
		self::clearError();
		$socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if (!$socket) {
			self::$errCode = 10101;
			self::$errMsg  = @socket_strerror(@socket_last_error());
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$timeout, "usec"=>$utmo))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout, "usec"=>$utmo))){
			self::$errCode = 10106;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket));
			@socket_close($socket);
			return false;
		}
		$n = self::udpSocketSendTo($socket, $cmd, $ip, $port);
		if ($n == false) {
			@socket_close($socket);
			return false;
		}
		if ($isResponse === false) {
			return true;
		}
		return self::udpSocketRecvFrom($socket, 10240, $ip, $port);
	}

	/**
	 * TcpServer的客户端
	 *
	 * @param string ip      ip地址
	 * @param int	 port    端口
	 * @param string cmd     命令字符串
	 * @param int timeout_sec    超时秒
	 * @param int timeout_usec   超时u秒
	 *
	 * @return  string 正确返回接受到的数据,错误返回false
	 */
	public static function tcpPHPCmd($ip, $port, $cmd, $n = 2, $timeout_sec = 2, $timeout_usec = 0)
	{
		self::clearError();
		$hostInfo = " to {$ip}:{$port} ";	// 连接的 ip:port 信息
		$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!$socket) {
			self::$errCode = 10101;
			self::$errMsg  = @socket_strerror(@socket_last_error()) . $hostInfo;
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>$timeout_sec, "usec"=>$timeout_usec))){
			self::$errCode = 10106;
			self::$errMsg = @socket_strerror(@socket_last_error($socket)) . $hostInfo;
			@socket_close($socket);
			return false;
		}
		if(!@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>$timeout_sec, "usec"=>$timeout_usec))){
			self::$errCode = 10106;
			self::$errMsg = @socket_strerror(@socket_last_error($socket)) . $hostInfo;
			@socket_close($socket);
			return false;
		}
		$ret = false;
		for ($i = 0; $i < $n; $i++){
			$ret = @socket_connect($socket, $ip, $port);
			if ($ret == true) break;
		}
		if ($ret === false) {
			self::$errCode = 10107;
			self::$errMsg  = @socket_strerror(@socket_last_error($socket)) . $hostInfo;
			@socket_close($socket);
			return false;
		}
		$ret = self::tcpSocketWrite($socket, $cmd);
		if ( $ret===false ) {
			@socket_close($socket);
			return false;
		}
		$rev = self::tcpSocketRead($socket, 1024000);
		@socket_close($socket);
		return $rev;
	}

	/**
	 * 常用打包函数
	 *
	 * @author	hickwu
	 * @param	mix		$data	需要打包的数据
	 * @return	string
	 */
	public static function wrap($data) {
		$str = serialize($data);

		return $str;
	}

	/**
	 * 常用解包函数
	 *
	 * @author	hickwu
	 * @param	string		$str	需要解包的数据
	 * @return	mix			解包失败返回 false，成功返回打包数据
	 */
	public static function unwrap($str)
	{
		self::clearError();

		$arr = unserialize($str);

		if ($arr === false) {
			self::$errCode = 10613;
			self::$errMsg  = 'unserialize-err-' . serialize($str);
		}

		return $arr;
	}

	// ##################### Qzone 通用协议相关函数 ####################

	/**
	 * 求二进制串校验和
	 *
	 * @author		peterdu
	 *
	 * @param		string	$bytes, 二进制串
	 *
	 * @return		short	$sum, 校验和值
	 */
	public static function checkSum($bytes) {
		self::clearError();

		$len = strlen($bytes);

		$sum = 0;
		$tail = 0;

		if ( $len % 2 )
		{
			$tail = ord( substr($bytes, -1) );
		}

		$len = (int)($len / 2);

		for ($i = 0; $i < $len; $i++)
		{
			$chunk = substr($bytes, $i * 2, 2);

			$sum += array_pop( unpack('S', $chunk) );
		}

		$sum += $tail;

		$sum = ($sum >> 16) + ($sum & 0xffff);
		$sum += ($sum >> 16);

		return hexdec( substr( dechex(~$sum), 4 ) );
	}

	/**
	 * 构建 Qzone 通用协议包
	 *
	 * @author		peterdu
	 *
	 * @param		int			$ver, 协议版本(注意: 通用协议源文件中, 此处传入为字符类型, php 中调用需传入其 ascii 码)
	 * @param		int			$cmd, 协议命令字
	 * @param		string		$body, 协议包体
	 * @param		bool		$chk, 是否需要校验和, 可选参数, 默认为 false
	 * @param		int			$sn, 协议序列号, 可选参数, 默认为0
	 * @param		int			$color, 染色信息, 可选信息, 默认为0
	 *
	 * @return		string		$pack_str, 构建好的协议包
	 */
	public static function structQzoneCmmPrtcl($ver, $cmd, $body, $chk = false, $sn = 0, $color = 0) {
		self::clearError();

		// 协议头标识字符
		$pre_ch = chr(4);

		// 协议版本号
		$ver = intval($ver);
		$ver = chr($ver);

		// 协议命令字
		$cmd = intval($cmd);
		$cmd = pack('N', $cmd);

		// 初始化校验和
		$init_checksum = pack('n', 0);

		// 协议序列号
		$sn = intval($sn);
		$sn = pack('N', $sn);

		// 协议染色信息
		$color = intval($color);
		$color = pack('N', $color);

		// 回应标识
		$respflag = chr(100);

		// 回应信息
		$respinfo = pack('n', 0);

		// 保留字段
		$reserve = chr(0);

		// 协议包长度
		$head_len = 25;
		$body_len = strlen($body);
		$pack_len = pack('N', $head_len + $body_len);

		// 协议尾标识字符
		$tail_ch = chr(5);

		$pack_str = '';
		$pack_str_1 = '';
		$pack_str_2 = '';

		$pack_str_1 .= $pre_ch;
		$pack_str_1 .= $ver;
		$pack_str_1 .= $cmd;

		$pack_str_2 .= $sn;
		$pack_str_2 .= $color;
		$pack_str_2 .= $respflag;
		$pack_str_2 .= $respinfo;
		$pack_str_2 .= $reserve;
		$pack_str_2 .= $pack_len;
		$pack_str_2 .= $body;
		$pack_str_2 .= $tail_ch;

		$pack_str = $pack_str_1 . $init_checksum . $pack_str_2;

		if ( $chk ) {
			// 计算实际校验和
			$act_checksum = self::checkSum($pack_str);
			$act_checksum = pack('s', $act_checksum);

			$pack_str = $pack_str_1 . $act_checksum . $pack_str_2;
		}

		return $pack_str;
	}

	/**
	 * 解析 Qzone 通用协议包
	 *
	 * @author		peterdu
	 *
	 * @param		string			$pack_str, 协议包(二进制串)
	 * @param		bool			$chk, 是否检测校验和, 可选参数, 默认为 false
	 *
	 * @return		array/bool		$pack, 为协议包解析后的数组, 元素包括协议版本, 协议命令字, 回应标识, 协议体等, 错误返回 false
	 */
	public static function parseQzoneCmmPrtcl($pack_str, $chk = false) {
		self::clearError();

		$data = array();
		$data['errcode'] = 0;
		$data['errmsg'] = '';

		$pack_len = strlen($pack_str);

		if ( !$pack_len ) {
			self::$errCode = 10612;
			self::$errMsg = 'empty package';

			return false;
		}

		// 若检测校验和
		if ( $chk ) {
			$chksum = self::checkSum($pack_str);

			if ( $chksum != 0x0 && $chksum != 0xffff )
			{
				self::$errCode = 10613;
				self::$errMsg = 'validate checksum failed';

				return false;
			}
		}

		// 均转换为10进制, 避免使用 unpack 引起符号问题
		$ver		= hexdec( bin2hex( substr($pack_str, 1, 1) ) );
		$cmd		= hexdec( bin2hex( substr($pack_str, 2, 4) ) );
		$checksum	= hexdec( bin2hex( substr($pack_str, 6, 2) ) );
		$sn			= hexdec( bin2hex( substr($pack_str, 8, 4) ) );
		$color		= hexdec( bin2hex( substr($pack_str, 12, 4) ) );
		$respflag	= hexdec( bin2hex( substr($pack_str, 16, 1) ) );
		$respinfo	= hexdec( bin2hex( substr($pack_str, 17, 2) ) );
		$packlen	= hexdec( bin2hex( substr($pack_str, 20, 4) ) );

		if ( $packlen != $pack_len ) {
			self::$errCode = 10614;
			self::$errMsg = 'package length is not match';

			return false;
		}

		// 截取包体
		$body		= substr($pack_str, 24, -1);

		$pack				= array();
		$pack['ver']		= $ver;
		$pack['cmd']		= $cmd;
		$pack['checksum']	= $checksum;
		$pack['sn']			= $sn;
		$pack['color']		= $color;
		$pack['respflag']	= $respflag;
		$pack['respinfo']	= $respinfo;
		$pack['packlen']	= $packlen;
		$pack['body']		= $body;

		return $pack;
	}

	// ##################### cURL 请求相关函数 ####################

	/**
	 * 使用 cURL 实现 HTTP GET 请求
	 *
	 * @param		string			$url, 请求地址
	 * @param		string			$host, 服务器 host 名, 默认为空(当一台机器有多个虚拟主机时需要指定 host)
	 * @param		int				$timeout, 连接超时时间, 默认为2
	 *
	 * @return		sting/bool		$data, 为返回数据, 失败返回 false
	 */
	public static function cURLHTTPGet($url, $timeout = 2, $host = '') {
		self::clearError();

		$header = array('Content-transfer-encoding: text');

		if ( !empty($host) ) {
			$header[] = 'Host: ' . $host;
		}

		$curl_handle = curl_init();

		// 连接超时
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
		// 执行超时
		curl_setopt($curl_handle, CURLOPT_TIMEOUT, 3);
		// HTTP返回错误时, 函数直接返回错误
		curl_setopt($curl_handle, CURLOPT_FAILONERROR, true);
		// 允许重定向
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
		// 允许重定向的最大次数
		curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 2);
		// 返回为字符串
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		// 设置HTTP头
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
		// 指定请求地址
		curl_setopt($curl_handle, CURLOPT_URL, $url);

		// 执行请求
		$response = curl_exec($curl_handle);

		if ( $response === false ) {
			self::$errCode = 10615;
			self::$errMsg = 'cURL errno: ' . curl_errno($curl_handle) . '; error: ' . curl_error($curl_handle);
			// 关闭连接
			curl_close($curl_handle);

			return false;
		}

		// 关闭连接
		curl_close($curl_handle);

		return $response;
	}
	/**
	 * 使用 cURL 实现 HTTP POST 请求
	 *
	 * @param		string			$url, 请求地址
	 * @param		string			$post_data, 请求的post数据，一般为经过urlencode 和用&处理后的字符串
	 * @param		string			$host, 服务器 host 名, 默认为空(当一台机器有多个虚拟主机时需要指定 host)
	 * @param		int				$timeout, 连接超时时间, 默认为2
	 *
	 * @return		sting/bool		$data, 为返回数据, 失败返回 false
	 */
	public static function cURLHTTPPost($url, $post_data, $timeout = 2, $host = '') {
		self::clearError();
		$data_len = strlen($post_data);
		$header = array('Content-transfer-encoding: text', 'Content-Length: ' . $data_len);

		if ( !empty($host) ) {
			$header[] = 'Host: ' . $host;
		}

		$curl_handle = curl_init();

		// 连接超时
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
		// 执行超时
		curl_setopt($curl_handle, CURLOPT_TIMEOUT, 3);
		// HTTP返回错误时, 函数直接返回错误
		curl_setopt($curl_handle, CURLOPT_FAILONERROR, true);
		// 允许重定向
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
		// 允许重定向的最大次数
		curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 2);
		// 返回为字符串
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		// 设置HTTP头
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
		// 指定请求地址
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		//设置为post方式
		curl_setopt($curl_handle, CURLOPT_POST, TRUE);
		//post 参数
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $post_data);

		// 执行请求
		$response = curl_exec($curl_handle);
		if ( $response === false ) {
			self::$errCode = 10616;
			self::$errMsg = 'cURL errno: ' . curl_errno($curl_handle) . '; error: ' . curl_error($curl_handle);
			// 关闭连接
			curl_close($curl_handle);

			return false;
		}

		// 关闭连接
		curl_close($curl_handle);

		return $response;
	}

	/**
	 * 使用 cURL 实现 HTTPS GET 请求
	 *
	 * @param		string			$url, 请求地址
	 * @param		string			$host, 服务器 host 名, 默认为空(当一台机器有多个虚拟主机时需要指定 host)
	 * @param		int				$timeout, 连接超时时间, 默认为2
	 *
	 * @return		sting/bool		$data, 为返回数据, 失败返回 false
	 */
	public static function cURLHTTPSGet($url, $timeout = 30, $host = '') {
		self::clearError();

		$header = array('Content-transfer-encoding: text');

		if ( !empty($host) ) {
			$header[] = 'Host: ' . $host;
		}

		$curl_handle = curl_init();

		// 连接超时
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, $timeout);
		// 执行超时
		curl_setopt($curl_handle, CURLOPT_TIMEOUT, 30);
		// HTTP返回错误时, 函数直接返回错误
		curl_setopt($curl_handle, CURLOPT_FAILONERROR, true);
		// 允许重定向
		curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, true);
		// 允许重定向的最大次数
		curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 5);
		// 返回为字符串
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		// 设置HTTP头
		curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $header);
		// 指定请求地址
		curl_setopt($curl_handle, CURLOPT_URL, $url);

		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($curl_handle, CURLOPT_USERAGENT,"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:43.0) Gecko/20100101 Firefox/43.0"); 


		// 执行请求
		$response = curl_exec($curl_handle);

		if ( $response === false ) {
			self::$errCode = 10615;
			self::$errMsg = 'cURL errno: ' . curl_errno($curl_handle) . '; error: ' . curl_error($curl_handle);
			// 关闭连接
			curl_close($curl_handle);
			echo self::$errMsg;die;
			return false;
		}

		// 关闭连接
		curl_close($curl_handle);

		return $response;
	}


	/**
	 * 维护分表统一自增字段
	 *
	 * @param Mixed $code
	 * @return 获取的可插入DB的ID
	 */
	public static function getAutoId($code){
		self::clearError();
		if(empty($code) || !is_numeric($code)){
			self::$errCode = 5001;
			self::$errMsg = 'autoid code err';
			return false;
		}

		$autoIdSvr = Config::getIP('autoId');
		if($autoIdSvr === false){
			self::$errCode = Config::$errCode;
			self::$errMsg = Config::$errMsg;
			return false;
		}

		$bag = array(
					'code'=>intval($code)
				);
		$bag = self::wrap($bag);
		$rev = self::tcpPHPCmd($autoIdSvr['IP'], $autoIdSvr['PORT'], $bag);
		if($rev === false){
			self::$errCode = self::$errCode;
			self::$errMsg = self::$errMsg;
			return false;
		}

		$rev = self::unwrap($rev);

		if(!is_array($rev)){
			self::$errCode = 5002;
			self::$errMsg = 'server returns errno ' . $rev;
			return false;
		}

		if(!isset($rev['value'])){
			self::$errCode = 5003;
			self::$errMsg = 'server returns no value';
			return false;
		}

		return intval($rev['value']);
	}


}
