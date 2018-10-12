<?php
// +----------------------------------------------------------------------
// | 充值
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RechargeController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->recharge = D("recharge");

	}

	//列表显示
    public function index(){
    	$username = I('request.username');//所属分类
		
		$where['username'] = array('like',"%$username%");
		
		$count=$this->recharge->join("lgq_user on lgq_user.id = lgq_recharge.user_id")->where($where)->count();
		$page = $this->page($count,11);
		$recharge = $this->recharge
			->join('lgq_user on lgq_user.id = lgq_recharge.user_id')
			->field('lgq_user.username,lgq_recharge.*')
            ->where($where)
            ->order("lgq_recharge.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

		$this->assign("page", $page->show());
		$this->assign("rechargelist",$recharge);
		
        $this->display('index');
    }
    
  
	

}