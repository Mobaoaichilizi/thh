<?php
// +----------------------------------------------------------------------
// | 收支明细
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class FinancialController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->financial = D("Financial");
		$this->user  = D('User');
	}

	//列表显示
    public function index(){
    	
		$count=$this->financial->count();
		$page = $this->page($count,11);
		$financial = $this->financial
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($financial as $k => &$v) {
        	$v['username'] = $this->user->where('id='.$v['user_id'])->getField('username');
        }
		$this->assign("page", $page->show());
		$this->assign("financiallist",$financial);
        $this->display('index');
    }
    
  
	

}