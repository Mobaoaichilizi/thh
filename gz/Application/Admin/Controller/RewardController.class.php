<?php
// +----------------------------------------------------------------------
// | 打赏管理
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class RewardController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->reward = D("Reward");
		$this->member = D('Member');
		$this->patientmember = D('Patientmember');
	}

	//列表显示
    public function index(){
		$status = I('request.status');
		if($status){
			if($status == "否"){
				$status = 0;
			}else if($status == "是"){
				$status = 1;
			}
			$where['status'] = array('like',"%$status%");
		}
    	
		
		$count=$this->reward->where($where)->count();
		$page = $this->page($count,11);
		$reward = $this->reward
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($reward as $key => $value) {
			

        	$value1 = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$value['member_id'])->field('name,username')->find();
        
        	$value2 = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$value['patientmember_id'])->field('name,username')->find();
        	$value['doctorusername'] = $value1['username'];
	    	$value['doctorname'] = $value1['name'];
	    	$value['patientusername'] = $value2['username'];
	    	$value['patientname'] = $value2['name'];
        	$userst[]=$value;
        }
        // dump($userst);
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
		
        $this->display('index');
    }
    
	

}