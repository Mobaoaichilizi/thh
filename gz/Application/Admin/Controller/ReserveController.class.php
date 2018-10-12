<?php
// +----------------------------------------------------------------------
// | 预约诊疗
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class ReserveController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->reserve = D("reserve");
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
		$count=$this->reserve->where($where)->count();
		$page = $this->page($count,11);
		$reserve = $this->reserve
            ->where($where)
            ->order("id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
       foreach ($reserve as  $value) {
			

        	$value['username'] = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$value['member_id'])->getfield('username');
        	$value['patientusername'] = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$value['patientmember_id'])->getfield('username');
        	$userst[]=$value;
        }
		$this->assign("page", $page->show());
		$this->assign("list",$userst);
        $this->display('index');
    }
    
    //查看详细信息
    public function lookReserve()
	{
	
		$id = I('get.id',0,'intval');
		$reserve = $this->reserve->where('id='.$id)->order("id DESC")->find();
			
    	$reserve1 = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->where('lgq_user.id='.$reserve['member_id'])->field('username,name')->find();

    	$reserve2 = $this->patientmember->join('lgq_user on lgq_user.id = lgq_patientmember.user_id')->where('lgq_user.id='.$reserve['patientmember_id'])->field('username,name')->find();

    	$reserve['doctorusername'] = $reserve1['username'];
    	$reserve['doctorname'] = $reserve1['name'];
    	$reserve['patientusername'] = $reserve2['username'];
    	$reserve['patientname'] = $reserve2['name'];
        	
		$this->assign('reserve',$reserve);
		$this->display('lookReserve');
		
		
	}
	public function addPay(){

		if(IS_POST){
			if($this->member->create()!==false) {
				$money = I('post.money');
				$res  = array(
					'reserve_price' => $money,
				);
				$result = $this->member->where("1=1 and user_id!=''")->save($res);

				if ($result!==false) {
					
					$this->success("修改成功！", U("Reserve/index"));
				}else
				{
					$this->error('修改失败!');
				}
			}
		}else
		{
			$money=$this->member->order('id desc')->limit(1)->getField('reserve_price');
			$this->assign('money',$money);
			$this->display('addPay');
		}
		
	}
	

}