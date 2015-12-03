<?php
/* *
 * 支付宝接口RSA函数
 * 详细：RSA签名、验签、解密
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。
 */

/**
 * RSA签名
 * @param $data string 待签名数据
 * @param $priKeyPath string 商户私钥文件路径
 * @return string 签名结果
 */
function rsaSign($data, $priKeyPath) {
    $priKey = file_get_contents($priKeyPath);
    $res = openssl_get_privatekey($priKey);
    openssl_sign($data, $sign, $res);
    openssl_free_key($res);
    $sign = base64_encode($sign);
    return $sign;
}

/**
 * RSA验签
 * @param $data 待签名数据
 * @param $pubKeyPath 支付宝的公钥文件路径
 * @param $sign 要校对的的签名结果
 * @return 验证结果
 */
function rsaVerify($data, $pubKeyPath, $sign)  {
    $pubKey = file_get_contents($pubKeyPath);
    $res = openssl_get_publickey($pubKey);
    $result = (bool)openssl_verify($data, base64_decode($sign), $res);
    openssl_free_key($res);
    return $result;
}

/**
 * RSA解密
 * @param $content 需要解密的内容，密文
 * @param $priKeyPath 商户私钥文件路径
 * @return 解密后内容，明文
 */
function rsaDecrypt($content, $priKeyPath) {
    $priKey = file_get_contents($priKeyPath);
    $res = openssl_get_privatekey($priKey);
    //用base64将内容还原成二进制
    $content = base64_decode($content);
    //把需要解密的内容，按128位拆开解密
    $result  = '';
    $blockSize = 128; 
    $run = strlen($content) / $blockSize;
    for ($i = 0; $i < $run; $i ++) {
        $data = substr($content, $i * $blockSize, $blockSize);
        openssl_private_decrypt($data, $decrypt, $res);
        $result .= $decrypt;
    }
    openssl_free_key($res);
    return $result;
}

