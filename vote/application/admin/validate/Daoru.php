<?php

// +----------------------------------------------------------------------

// | LGQ

// +----------------------------------------------------------------------

// | Author: thh <752106024@qq.com>

// +----------------------------------------------------------------------

namespace app\admin\validate;



use think\Validate;

class Daoru extends Validate

{

    protected $rule = [

        'number' => 'require'

    ];



    protected $message = [

        'number.require' => '不能为空'

    ];



}