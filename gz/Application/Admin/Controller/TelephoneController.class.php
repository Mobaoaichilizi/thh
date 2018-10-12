<?php
// +----------------------------------------------------------------------
// | 电话咨询
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class TelephoneController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->telephone = D("telephone");
		$this->user = D("user");
		$this->member = D("member");
		$this->patientmember = D("patientmember");

	}

	//列表显示
    public function index(){
    	$status = I('request.status');
		if($status){
			
			$where['status'] = array('eq',$status);
		}
		$count=$this->telephone->where($where)->count();
		$page = $this->page($count,11);
		$telephone = $this->telephone
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
        foreach ($telephone as  $value) {
			

        	$value['username'] = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$value['member_id'])->getfield('username');
        	$value['patientusername'] = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$value['patientmember_id'])->getfield('username');
        	$userst[]=$value;
        }    
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    
    //查看详细信息
    public function lookTelephone()
	{
	
		$id = I('get.id',0,'intval');
		$telephone = $this->telephone->where('id='.$id)->order("id DESC")->find();
			
    	$telephone1 = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$telephone['member_id'])->field('username,name')->find();

    	$telephone2 = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$telephone['patientmember_id'])->field('username,name')->find();

    	$telephone['doctorusername'] = $telephone1['username'];
    	$telephone['doctorname'] = $telephone1['name'];
    	$telephone['patientusername'] = $telephone2['username'];
    	$telephone['patientname'] = $telephone2['name'];
        	
		$this->assign('telephone',$telephone);
		$this->display('lookTelephone');
		
		
	}
	

}