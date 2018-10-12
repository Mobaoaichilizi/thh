<?php
// +----------------------------------------------------------------------
// | 函数文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
/**
 * 查询文章列表，不做分页；
 */
 //验证uid
 function accessTokenCheck($uid){
    $verifyCode='lgq_lovezhuan_yangsheng';
	$res_id=M("User")->where("md5(md5(id))='".$uid."'")->find();
    if(!$res_id){
       $result['code']=-1; 
       $result['message']='请先登录！';     
       return outJson($result);
    }
    
}
 //验证版本
 function accessVersionCheck($version){
	$res=M("Version")->where("version='".$version."'")->order("createtime desc")->find();
    if(!$res){
	   $res_t=M("Version")->order("createtime desc")->find();
       $result['code']=10001; 
       $result['message']='请更新版本';
	   $result['info']=array(
		'version' => $res_t['version'],
		'ver_desc' => $res_t['ver_desc'],
		'ver_url' => $res_t['ver_url'],
		'is_mandatory' => $res_t['is_mandatory'],
	   );
       return outJson($result);
    }  
}
 //单点登录
 function accessDeviceidCheck($uid,$deviceid){
	$res_id=M("User")->where("md5(md5(id))='".$uid."' and role=2")->find();
	if(!$res_id){
       $result['code']=10002; 
       $result['message']='请登录';     
       return outJson($result);
    }
    if($res_id['deviceid']!=$deviceid){
       $result['code']=10002; 
       $result['message']='请登录';
       return outJson($result);
    }  
}
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
