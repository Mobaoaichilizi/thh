<?php
// +----------------------------------------------------------------------
// | LGQ
// +----------------------------------------------------------------------
// | Author: thh <752106024@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;

class Teacher extends Validate
{
    protected $rule = [
        'name' => 'require|length:2,10|unique:teacher'
    ];

    protected $message = [
        'name.require' => '教师名不能为空',
        'name.length' => '教师名必须是2-10个数字',
        'name.unique' => '教师名已存在'
    ];

}