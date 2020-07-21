<?php
// +----------------------------------------------------------------------
// | 公共函数
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2019 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
use think\Request;

/**
 * 权限检测
 * @param $uid
 * @param null $name
 * @param string $relation
 * @return bool
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\ModelNotFoundException
 * @throws \think\exception\DbException
 */
function sp_auth_check($uid,$name=null,$relation='or'){
	if(empty($uid)){
		return false;
	}

	if(empty($name)){
        $request = Request::instance();
		$name=strtolower($request->module()."/".$request->controller()."/".$request->action());
	}
	$access=think\Db::name('Access');
	$users=think\Db::name('Admin');
	$users_list=$users->where("id=".$uid)->find();
	$auth_list=$access->where("role_id=".$users_list['role_id'])->select();
	foreach($auth_list as $rule)
	{
		$list[]=strtolower($rule['node_name']);
	}
	if(!in_array($name,$list) ){
		return false;
	}else{
		return true;
	}
}
/**
 * 获得用户的真实IP地址
 */
function realIp()
{
    static $realip = NULL;
    if ($realip !== NULL) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = \explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip) {
                $ip = \trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $realip = $_SERVER['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (\getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = \getenv('HTTP_X_FORWARDED_FOR');
        } elseif (\getenv('HTTP_CLIENT_IP')) {
            $realip = \getenv('HTTP_CLIENT_IP');
        } else {
            $realip = \getenv('REMOTE_ADDR');
        }
    }
    \preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $realip;
}
