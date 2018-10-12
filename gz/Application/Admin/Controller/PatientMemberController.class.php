<?php
// +----------------------------------------------------------------------
// | 会员登录
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class PatientMemberController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->patientmember = D("patientmember");
		$this->user  = D('User');

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
    	$member_level = I('request.member_level');
    	$status = I('request.status');
		if($name){
			$where['lgq_patientmember.name'] = array('like',"%$name%");
			$where['lgq_user.role'] = array('eq',2);
		}
		if($member_level!==''){
			$where['lgq_user.member_level'] = array('eq',$member_level);
		}
		if($status!==''){
			$where['lgq_patientmember.status'] = array('eq',$status);
		}
		$where['lgq_user.id'] = array("neq",0);
		$count=$this->patientmember->join("lgq_user on lgq_user.id = lgq_patientmember.user_id")->where($where)->count();
		$page = $this->page($count,11);
		$patientmember = $this->patientmember->join("lgq_user on lgq_user.id = lgq_patientmember.user_id")->field("lgq_user.username,lgq_user.member_level,lgq_user.createtime,lgq_patientmember.*")
            ->where($where)
            ->order("lgq_patientmember.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
		$this->assign("page", $page->show());
		$this->assign("patientmemberlist",$patientmember);
        $this->display('index');
    }
    public function btn_export(){
	 	$xlsName  = "Patient";
        $xlsCell  = array(
            array('id','序列'),
            array('name','姓名'),
            array('sex','性别'),
            array('username','电话'),
            array('member_level','会员'),
            array('status','状态'),
            array('createtime','注册时间')
        );
        $xlsModel = $this->patientmember;
        $xlsData  = $xlsModel->Field('id,user_id,name,sex,status')->select();
        foreach ($xlsData as $k => &$v) {
        	$v['username'] = $this->user->where('id='.$v['user_id'])->getField('username');
        	$v['member_level'] = $this->user->where('id='.$v['user_id'])->getField('member_level');
        	
        	if($v['status'] == 0){
        		$v['status'] = "异常";
        	}else if($v['status'] == 1){
        		$v['status'] = "正常";
        	}
        	if($v['sex'] == 1){
        		$v['sex'] = "女";
        	}else if($v['sex'] == 2){
        		$v['sex'] = "男";
        	}else if($v['sex'] == 0){
        		$v['sex'] = "未知";
        	}
        	if($v['member_level'] == 1){
        		$v['member_level'] = "VIP";
        	}else if($v['member_level'] == 2){
        		$v['member_level'] = "SVIP";
        	}else if($v['member_level'] == 0){
        		$v['member_level'] = "非会员";
        	}
        	$v['createtime'] = $this->user->where('id='.$v['user_id'])->getField('createtime');
        	$v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
        }
        exportExcel($xlsName,$xlsCell,$xlsData);
	} 
    
    //查看详细信息
    public function lookPatientMember()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			$user_id = I('post.user_id',0,'intval');
			$balance = I('post.balance');
			$operation = I('post.operation');
			$res = array(
						"id" => $id,
						"status"=> $_POST['status'],
					);
			$ress = array(
						"id" => $_POST['user_id'],
						"member_level"=>$_POST['member_level'],
			);
			if($this->patientmember->create()!==false) {
				if ($patientmember!==false) {
					$patientmember=$this->patientmember->where("id=".$id)->save();
					$level = $this->user->save($ress);
					
					$result=$this->user->where("id=".$user_id)->find();
					if($balance!='')
					{
						if($operation=="+")
						{
							$this->user->where("id=".$user_id)->setInc('balance',$balance);
							$data=array(
					           'user_id'    => $user_id, 
					           'role'   => '2',
					           'score'  => $balance,
					           'content' => '元',
					           'setting_id'  => '167',
					           'optime' => time(),
						    );
						    M('ScoreList')->add($data);   
							
							$ba = $this->user->where('id='.$user_id)->getField('balance');
							financial_log($user_id,$balance,3,$ba,'系统后台操作',8);

						}else if($operation=="-")
						{
							if($result['balance']-$balance < 0)
							{
								$this->error('充值以后余额不能为负数!');
							}else
							{

								$this->user->where("id=".$user_id)->setDec('balance',$balance);
								$ba = $this->user->where('id='.$user_id)->getField('balance');
								financial_log($user_id,$balance,3,$ba,'系统后台操作',8);
							}
						}
					}
					
					$this->success("编辑成功！", U("patientmember/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$patientmember = $this->patientmember->join("lgq_user on lgq_user.id = lgq_patientmember.user_id")->field("lgq_user.username,lgq_user.member_level,lgq_patientmember.*")->where("lgq_patientmember.id=".$id)->find();
			$this->assign('patientmember',$patientmember);
			$this->display('lookPatientMember');
		}
		
		
	}
	

   
	



}