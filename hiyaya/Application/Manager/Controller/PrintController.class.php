<?php
// +----------------------------------------------------------------------
// | 打印设置
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Manager\Controller;
use Common\Controller\ManagerbaseController;
class PrintController extends ManagerbaseController {
	
	public function _initialize() {
		parent::_initialize();
		$this->Shop = M("shop");
		$this->shopid=session('shop_id');
		$this->chainid=session('chain_id');
	}
    public function index()
	{
		$shop_info=$this->Shop->where("id=".$this->shopid)->find();
	    $this->assign('shop_info',$shop_info);
        $this->display('index');
    }
}
?>