<?php
// +----------------------------------------------------------------------
// | 系统公共文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace app\common\controller;
use app\common\controller\Basic;
use think\Url;
use think\Db;
use think\Request;

class HomeBasic extends Basic
{

    public function __construct() {
		parent::__construct();
		$time=time();
		$this->assign("js_debug",$time);
   }
 
}
