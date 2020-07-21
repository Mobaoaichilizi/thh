<?php
// +----------------------------------------------------------------------
// | 函数文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
//返回josn格式
function outJson($result) {
// 返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:text/html; charset=utf-8');
	//exit(print_r($result));
    exit(json_encode($result));
}

/**

 * 字符串截取，支持中文和其他编码

 * @static

 * @access public

 * @param string $str 需要转换的字符串

 * @param string $start 开始位置

 * @param string $length 截取长度

 * @param string $charset 编码格式

 * @param string $suffix 截断显示字符

 * @return string

 */

function mssubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {

    if(function_exists("mb_substr"))

        $slice = mb_substr($str, $start, $length, $charset);

    elseif(function_exists('iconv_substr')) {

        $slice = iconv_substr($str,$start,$length,$charset);

        if(false === $slice) {

            $slice = '';

        }

    }else{

        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";

        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";

        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";

        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

        preg_match_all($re[$charset], $str, $match);

        $slice = join("",array_slice($match[0], $start, $length));

    }

    return $suffix ? $slice.'' : $slice;

}
