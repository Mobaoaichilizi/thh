<?php
// +----------------------------------------------------------------------
// | 用户地址管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PersonAddressController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->personAddress = D("Personaddress");
		$this->member = D("Member");
	}

	//列表显示
    public function index(){
    	$person = I('request.person');//银行信息
		
		$where['person'] = array('like',"%$person%");
		
		$count=$this->personAddress->where($where)->count();
		$page = $this->page($count,11);
		$personAddress = $this->personAddress
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();

		$this->assign("page", $page->show());
		$this->assign("personAddresslist",$personAddress);
		
        $this->display('index');
    }
    
	

}