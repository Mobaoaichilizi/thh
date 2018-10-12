<?php
// +----------------------------------------------------------------------
// | 银行账号管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class BankController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->bank = D("bank");
		$this->member = D("Member");
		$this->setting = D("Setting");
	}

	//列表显示
    public function index(){
    	$personname = I('request.personname');//银行信息
		$setting_id = I('request.setting_id');
		$where['personname'] = array('like',"%$personname%");
		if($setting_id){
			$where['setting_id'] = array('eq',$setting_id);
		}
		$count=$this->bank->where($where)->count();
		$page = $this->page($count,11);
		$bank = $this->bank
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $categories = $this->setting->where('parentid = 29')->select();
		$this->assign("page", $page->show());
		$this->assign("banklist",$bank);
		$this->assign('categories',$categories);
        $this->display('index');
    }
    
	 //查看详细信息
    public function lookBank()
	{
	
		$id = I('get.id',0,'intval');
		$bank = $this->bank->where('id='.$id)->find();
		$this->assign('bank',$bank);
		$this->display('lookBank');
		
		
	}
	

}