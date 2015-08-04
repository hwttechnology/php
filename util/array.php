<?
/**
 * 除去数组中的空值和签名参数
 * @param $para 签名参数组
 * @return 去掉空值与签名参数后的新签名参数组
 */
function paraFilter($para) {
    $para_filter = array();
    while (list ($key, $val) = each ($para)) {
        if ($key == "sign" || $key == "sign_type" || $val == "") {
            continue;
        }
        else {
            $para_filter[$key] = $para[$key];
        }
    }
    return $para_filter;
}
/**
 * 对数组排序
 * @param $para 排序前的数组
 * @return 排序后的数组
 */
function argSort($para) {
    ksort($para);
    reset($para);
    return $para;
}
