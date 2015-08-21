<?php
/**
 * 数组转CSV
 */
function toCSV($arr)
{
    if (! is_array($arr)) {
        return strval($arr);
    }
    return implode(",", $arr);
}

/**
 * 强制转换为数组
 */
function toArray($val)
{
    if (is_array($val)) {
        return $val;
    }
    return array($val);
}

/**
 * 检测参数是否都有设置
 */
function AnyEmpty()
{
    $args = func_get_args();
    foreach ($args as $arg) {
        if (empty($arg)) {
            return true;
        }
    }
    return false;
}

/**
 * 为url添加?号
 * @param string $url get请求的url
 * @return string 以问号结尾的url
 */
function questionMark($url)
{
    if (substr($url, -1, 1) == "?") {
        return $url;
    }
    return $url . "?";
}

/**
 * 拼接get请求url
 * @param string $url 请求url，不带任何参数
 * @param mixed  $param 请求参数，可以是array或string类型
 * @return string get请求的url
 */
function buildGetUrl($url, $param)
{
    if (empty($url)) {
        return "";
    }
    if (is_array($param)) {
        $param = http_build_query($param);
    }
    return questionMark($url) . $param;
}

/**
 * 按照指定的key，重排数组
 * @param array $param key-value数组
 * @param array $seq key的顺序，不在$key内的key会被忽略
 * @return array 按$seq给出的key顺序重排后的数组
 */
function resort($param, $seq)
{
    $res = array();
    foreach ($seq as $key) {
        $res[$key] = $param[$key];
    }
    return $res;
}

/**
 * 判断字符串相等性，简化丑陋的0 === strcmp
 * @param string $str1 字符串1
 * @param string $str2 字符串2
 * @param bool $case_sensitive 是否区分大小写
 * @return bool true: 相等; false:不相等
 */
function isStrEqual($str1, $str2, $case_sensitive = false)
{
    if ($case_sensitive) {
        return 0 === strcmp($str1, $str2);
    } else {
        return 0 === strcasecmp($str1, $str2);
    }
}

/**
 * 一致性hash算法(redis等使用)
 * @param mixed $key key值
 * @param int $count 节点总数
 * @return int 值域：[0, $count-1]
 */
function consistantHash ($key, $count)
{
    return hexdec(substr(md5('-' . $key), 8, 5)) % $count;
}

/**
 * 发送http get请求
 * @param string $url 完整的请求url
 * @param string $msg 错误消息
 * @return mixed
 */
function httpGet($url, &$msg)
{
    $ch = curl_init();
    if (! $ch) {
        return false;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, true);

    $data = curl_exec($ch);

    $msg = curl_error($ch);
    $info = curl_getinfo($ch);

    if ($info) {
        // log info
    }

    curl_close($ch);

    return $data;
}

/**
 * 发送http post请求
 * @param string $url 完整的请求url
 * @param string $data 请求body
 * @param string $type 请求body的Content-Type
 * @param array  $header 需要添加的http header
 * @return mixed
 */
function httpPost($url, $data, $type = "xml", $headers = array())
{
    $ch = curl_init();
    if (! $ch) {
        return false;
    }

    if ($type == "xml") {
        $headers[] = "Content-Type: text/xml";
    } elseif ($type == "json") {
        $headers[] = "Content-Type: application/json";
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $ret = curl_exec($ch);
    curl_close($ch);

    return $ret;
}

/**
 * imsi查电信运营商
 * @param string $imsi 收集imsi
 * @return integer 0: NotFound 1: 移动 2: 联通 3: 电信
 */
function GetMobileSP($imsi)
{
    if (empty($imsi)) {
        return  0;
    }
    $code = substr($imsi, 0, 5);
    if ($code == "46000" || $code == "46002" || $code == "46007" || $code == "46020" || $code == "46008" || $code == "46060") {
        //中国移动
        return 1;
    }
    if ($code == "46001" || $code == "46006" || $code == "46010") {
        //中国联通
        return 2;
    }
    if ($code == "46003" || $code == "46005" || $code == "46011")  {
        //中国电信
        return 3;
    }
    return 0;
}

/**
 * 获取IP
 * @return string ip
 */
function getIp()
{
    define("YM_LOCAL_IP", "127.0.0.1");

    $ip = YM_LOCAL_IP;

    if (isset($_SERVER)) {
        #网宿的解决方案，握手ip放在 HTTP_CDN_SRC_IP
        if (isset($_SERVER['HTTP_CDN_SRC_IP']) && !empty($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        }
        #负载均衡传来的
        elseif (isset($_SERVER['HTTP_X_REAL_IP']) && !empty($_SERVER['HTTP_X_REAL_IP']))
        {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }
    }
    else {
        $ip = getenv('REMOTE_ADDR');
    }

    //如果不是本地ｉｐ则直接返回
    if (!isLocalIp($ip)) {
        return $ip;
    }

    if (isset($_SERVER)) {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            //如果是多个地址组成的字符串
            if (0 < ($pos = strpos($ip, ','))) {
                $ip = substr($ip, 0, $pos);
            }
        }
        elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
    }
    else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            //如果是多个地址组成的字符串
            if (0 < ($pos = strpos($ip, ','))) {
                $ip = substr($ip, 0, $pos);
            }
        }
        elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        else {
            $ip = getenv('REMOTE_ADDR');
        }
    }

    //如果是局域网ip的话，则返回握手协议中的ip
    if (isLocalIp($ip)) {
        return YM_LOCAL_IP;
    }

    return $ip;
}

/**
 * 判断ip是否是局域网ip
 * @param $dotip string
 * @return boolean
 */
function isLocalIp ($ip)
{
    if ('127.0.0.1' == $ip)
    {
        return true;
    }

    $ip = explode('.', $ip);
    if (10 == $ip[0])
    {
        return true;
    }
    elseif (172 == $ip[0] && $ip[1] > 15 && $ip[1] < 32)
    {
        return true;
    }
    elseif (192 == $ip[0] && 168 == $ip[1])
    {
        return true;
    }

    return false;
}

/**
 * 从文件内容生成数组，每行文本一个元素
 * @param string $file 文件名
 * @return array
 */
function file2array($file)
{
    if(! is_readable($file)) {
        return false;
    }
    $filecontent = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    return array_map('trim', $filecontent);
}

function param_filter($param, $keyset)
{
    $result = array();
    foreach ($keyset as $key) {
        $result[$key] = isset($param[$key]) ? $param[$key] : "";
    }
    return $result;
}


