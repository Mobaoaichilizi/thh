<?php
// +----------------------------------------------------------------------
// | LGQ
// +----------------------------------------------------------------------
// | Author: love_fat <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        ['app', 'require', '应用名称不能为空'],
        ['model', 'require', '控制器名称不能为空'],
        ['action', 'require', '方法名称不能为空'],
        ['name', 'require', '菜单名称不能空']
    ];

}