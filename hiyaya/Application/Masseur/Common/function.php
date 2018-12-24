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
//验证是否是手机号
function is_phone($tel)
{
    if (preg_match("/^1[34578]{1}\d{9}$/", $tel))
    {
       return ture;
    }
    else
    {
        return false;
    }
}


//验证用户是否登录
function accessTokenCheck($token){
	$res_id=M("ShopUser")->where("token='".$token."'")->find();
    if(!$res_id){
       $result['code']=10001; 
       $result['msg']='请先登录！';     
       return outJson($result);
    }
    
}

//验证版本
function accessVersionCheck($version){
	$res=M("Version")->where("version='".$version."'")->order("createtime desc")->find();
    if(!$res){
	   $res_t=M("Version")->order("createtime desc")->find();
       $result['code']=10002;
       $result['msg']='请更新版本';
	   $result['data']=array(
		'version' => $res_t['version'],
		'ver_desc' => $res_t['ver_desc'],
		'ver_url' => $res_t['ver_url'],
		'is_mandatory' => $res_t['is_mandatory'],
	   );
       return outJson($result);
    }  
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

//技师级别

function masseur_level($id)
{
	$result=M("MasseurCategory")->where("id=".$id)->find();
	if($result)
	{
		return $result['category_name'];
	}else
	{
		return '';
	}
}
//判断门店的某个字段的值是否唯一
function unique($where,$name,$parm,$table)
{
    $where.=" AND $name='".$parm."'";
    $count=$table->where($where)->count();
    if($count>0)
    {
       return false;
        exit;
    }
    else
    {
        return true;
    }
}

function getID()
{
    return session_id();
}

function destroy(){
    setcookie(session_name(), null, -1, '/');
    session_destroy();
}
//获取首字母
function Getzimu($str)
{
    $str= iconv("UTF-8","gb2312", $str);//如果程序是gbk的，此行就要注释掉
    if (preg_match("/^[\x7f-\xff]/", $str)) //判断是否全是中文
    {
        $fchar=ord($str{0});
        if($fchar>=ord("A") and $fchar<=ord("z") )return strtoupper($str{0});
        $a = $str;
        $val=ord($a{0})*256+ord($a{1})-65536;
        if($val>=-20319 and $val<=-20284)return "A";
        if($val>=-20283 and $val<=-19776)return "B";
        if($val>=-19775 and $val<=-19219)return "C";
        if($val>=-19218 and $val<=-18711)return "D";
        if($val>=-18710 and $val<=-18527)return "E";
        if($val>=-18526 and $val<=-18240)return "F";
        if($val>=-18239 and $val<=-17923)return "G";
        if($val>=-17922 and $val<=-17418)return "H";
        if($val>=-17417 and $val<=-16475)return "J";
        if($val>=-16474 and $val<=-16213)return "K";
        if($val>=-16212 and $val<=-15641)return "L";
        if($val>=-15640 and $val<=-15166)return "M";
        if($val>=-15165 and $val<=-14923)return "N";
        if($val>=-14922 and $val<=-14915)return "O";
        if($val>=-14914 and $val<=-14631)return "P";
        if($val>=-14630 and $val<=-14150)return "Q";
        if($val>=-14149 and $val<=-14091)return "R";
        if($val>=-14090 and $val<=-13319)return "S";
        if($val>=-13318 and $val<=-12839)return "T";
        if($val>=-12838 and $val<=-12557)return "W";
        if($val>=-12556 and $val<=-11848)return "X";
        if($val>=-11847 and $val<=-11056)return "Y";
        if($val>=-11055 and $val<=-10247)return "Z";
    }
    else
    {
        return false;
    }
}