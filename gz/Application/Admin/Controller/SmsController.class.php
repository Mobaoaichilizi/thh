<?php
// +----------------------------------------------------------------------
// | 短信发送管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SmsController extends AdminbaseController {
	protected $sms;
	
	public function _initialize() {
		parent::_initialize();
		$this->sms = D("Sms");
	}
    public function index(){
		$phone = I('request.phone');
		if($phone!=''){
			$sqlwhere.= " and phone=".$phone;
		}
		$count=$this->sms->where("1=1 ".$sqlwhere)->count();
		$page = $this->page($count,11);
		$list = $this->sms
			->where("1=1 ".$sqlwhere)
            ->order("createtime DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("list",$list);
        $this->display('index');
    }
	
}
?>