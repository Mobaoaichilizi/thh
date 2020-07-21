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
class CustomerController extends AdminbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->customer = D("member");
		$this->user     = D("user");

	}

	//列表显示
    public function index(){
    	$name = I('request.name');
		if($name){
			$where['lgq_user.name'] = array('like',"%$name%");
			$where['lgq_user.role'] = array('eq',1);
		}
		$count=$this->customer->where($where)->count();
		$page = $this->page($count,11);
		$customer = $this->customer->join("lgq_user on lgq_user.id = lgq_member.user_id")->join("left join lgq_department on lgq_department.id = lgq_member.department_id")->join("left join lgq_hospital on lgq_hospital.id = lgq_member.hospital_id")->field("lgq_department.name as departmentname,lgq_hospital.name as hospitalname,lgq_user.username,lgq_user.member_level,lgq_user.createtime,lgq_member.*")
            ->where($where)
            ->order("lgq_member.id DESC")
            ->limit($page->firstRow, $page->listRows)
            ->select();
       
		$this->assign("page", $page->show());
		$this->assign("customerlist",$customer);
        $this->display('index');
    }
    

 //    public function editCustomer()
	// {
	// 	if(IS_POST)
	// 	{
	// 		$id = I('post.id',0,'intval');
	// 		if($this->Customer->create()!==false) {
	// 			if ($Customer!==false) {
	// 				$Customer=$this->Customer->where("id=".$id)->save();
	// 				$this->success("编辑成功！", U("Customer/index"));
	// 			}else
	// 			{
	// 				$this->error('编辑失败!');
	// 			}
	// 		}
	// 	}else
	// 	{
	// 		$id = I('get.id',0,'intval');
	// 		$Customer=$this->Customer->where("id=".$id)->find();
	// 		$this->assign('Customer',$Customer);
	// 		$this->display('editCustomer');
	// 	}

		
	// }
    //查看详细信息
    public function lookCustomer()
	{
		if(IS_POST)
		{
			$id = I('post.id',0,'intval');
			$res = array(
						"id" => $id,
						"status"=> $_POST['status'],
					);
			if($this->customer->create()!==false) {
				if ($customer!==false) {

					$customer=$this->customer->where("id=".$id)->save($res);
					$this->success("编辑成功！", U("customer/index"));
				}else
				{
					$this->error('编辑失败!');
				}
			}
		}else
		{
			$id = I('get.id',0,'intval');
			$customer = $this->customer->join("lgq_user on lgq_user.id = lgq_member.user_id")->join("left join lgq_department on lgq_department.id = lgq_member.department_id")->join("left join lgq_hospital on lgq_hospital.id = lgq_member.hospital_id")->field("lgq_department.name as departmentname,lgq_hospital.name as hospitalname,lgq_user.username,lgq_user.member_level,role,lgq_member.*")->where("lgq_member.id=".$id)->find();
			if(!empty($customer['othercert'])){
				$customer['othercert'] = explode(",", $customer['othercert']);
			}
			$this->assign('customer',$customer);
			$this->display('lookCustomer');
		}


		
		
		
	}
public function audit()
	{

		$status = I('post.status');
		$id = I('post.id');
		$res = array(
			"id"     => $id,
			"stauts" => $stauts,
		);
		$result = $this->customer->save($res);
		$this->display('index');
	}	

   
	



}