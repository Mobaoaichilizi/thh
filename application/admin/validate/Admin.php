<?php
// +----------------------------------------------------------------------
// | LGQ
// +----------------------------------------------------------------------
// | Author: love_fat <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule =   [
        'admin_login'  => 'require|length:6,10|unique:admin',
        'admin_pwd'   => 'require|length:6,50'
    ];

    protected $message  =   [
        'admin_login.require' => '用户名不能为空',
        'admin_login.length'     => '用户名必须是6-10个字符',
        'admin_login.unique' => '用户名已存在',
        'admin_pwd.require'   => '密码不能为空',
        'admin_pwd.length'  => '密码必须是6-50个字符'
    ];

}