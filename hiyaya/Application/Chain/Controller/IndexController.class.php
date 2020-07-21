<?php
// +----------------------------------------------------------------------
// | 后台首页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Chain\Controller;
use Common\Controller\ChainbaseController;
class IndexController extends ChainbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->shop = M("Shop");
		$this->chainid=session('chain_id');
		$this->userid=session('user_id');
	}
    public function index()
	{
		$shop_count=$this->shop->where("chain_id=".$this->chainid." and state=1")->count();
		$this->assign('shop_count',$shop_count);
		$this->display('index');
    }
	public function welcome()
	{		
		$this->display('welcome');
	}
}
?>