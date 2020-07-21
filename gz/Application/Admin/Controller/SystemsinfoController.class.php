<?php
// +----------------------------------------------------------------------
// | 系统消息管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SystemsinfoController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->systemsinfo = D("SystemsInfo");
		$this->user = D("User");
		$this->member = D('Member');
		$this->patientmember = D('Patientmember');
	}

	//列表显示
    public function index(){
		$title = I('request.title');
		$where['title'] = array('like',"%$title%");
		$count=$this->systemsinfo->where($where)->count();
		$page = $this->page($count,11);
		$systemsinfo = $this->systemsinfo
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($systemsinfo as $key => $value) {
			

        	$value['send'] = $this->user->where("id=".$value['send_uid'])->getField('username');
        	if($value['receive_uid'] == "0_0"){
        		$value['receive'] = "全部用户";
        	}else if($value['receive_uid'] == "0_1"){
        		$value['receive'] = "全部医生";
        	}else if($value['receive_uid'] == "0_2"){
        		$value['receive'] = "全部患者";
        	}else{
        		$value['receive'] = $this->user->where("id=".(int)$value['receive_uid'])->getField('username');
        	}
        	
        	$userst[]=$value;
        }
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    //添加系统消息
    public function addSystemsinfo()
	{
		if(IS_POST){
			
			if($this->systemsinfo->create()!==false) {

				$result=$this->systemsinfo->add();

				if ($result!==false) {
					$this->success("添加成功！", U("Systemsinfo/index"));
				}else
				{
					$this->error('添加失败!');
				}
			}
		}else
		{
			$this->display('addSystemsinfo');
		}
	}
    
	

}