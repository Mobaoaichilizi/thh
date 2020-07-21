<?php
// +----------------------------------------------------------------------
// | 公共函数
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
use think\Db;

/**
 * 数据签名认证
 * @param array $data 被认证的数据
 * @return string       签名
 */
function user_auth_sign($data)
{
    //数据类型检测
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login()
{
    //$user = session('user_auth');
    $token = request()->post('token');
    if (empty($token)) {
        return 0;
    } else {
        $token_id = Db::name("User")->where("token='" . $token . "'")->value('id');
        if($token_id)
        {
            return $token_id;
        }else
        {
            return 0;
        }
    }
}

