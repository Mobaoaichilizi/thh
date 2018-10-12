<?php
// +----------------------------------------------------------------------
// | 我的信息管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MycenterController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patientmember =D("Patientmember");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');	
	}
	//个人信息展示
	public function myinfo()
	{
		$uid=$this->uid;
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$list=$this->patientmember->field("id,name,img_thumb,sex,age")->where("user_id=".$uid)->find();
		if($list)
		{
			$list['balance'] = $this->user->where("id=".$uid)->getField("balance");
			$list['score'] = $this->user->where("id=".$uid)->getField("score");
			$list['coupons'] = $this->user->where("id=".$uid)->getField("coupons");
			$list['phone'] = $this->user->where("id=".$uid)->getField("username");
			$list['member_level'] = $this->user->where("id=".$uid)->getField("member_level");
			$due_time = $this->user->where("id=".$uid)->getField("due_time");
			$t = $due_time-time();
			if($t > 0){
				$list['days_remaining'] = ceil($t/86400);
			}else{
				$list['days_remaining'] = 0;
			}
			unset($data);
			$data['code']=1;
			$data['message']="获取信息成功！";
			$data['info']=$list;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="暂无信息";
			outJson($data);	
		}
		
	}
	
	//编辑个人信息
	public function myinfo_edit()
	{
		$uid=$this->uid;
		$name=I('post.name');
		$img_thumb=I('post.img_thumb');
		$sex=I('post.sex');
		$age=I('post.age');
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入姓名！";
			outJson($data);	
		}
		$res=array(
			'name' => $name,
			'img_thumb' => $img_thumb,
			'sex' => $sex,
			'age' => $age,
		);
		$result=$this->patientmember->where("user_id=".$uid)->save($res);
		$rest=$this->patientmember->field("user_id,name,age,sex,status,img_thumb")->where("user_id=".$uid)->find();
	
		$resc=$this->user->where("id=".$rest['user_id'])->find();
		$rest['uid']=md5(md5($resc['id']));
		$rest['username']=$resc['username'];
		$rest['score']=$resc['score'];
		$rest['balance']=$resc['balance'];
		$rest['coupons']=$resc['coupons'];
		if($rest['name']=='')
		{
			$rest['is_perfect']=0;
		}else
		{
			$rest['is_perfect']=1;
		}
		unset($data);
		$data['code']=1;
		$data['message']="编辑成功！";
		$data['info'] = $rest;
		outJson($data);
		
	}
	
	
		//退出登陆
	public function logout()
	{	
		$id = $this->uid;
		if(empty($id) || $id=='NULL'  || $id=='null')
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);
		}
		$res=$this->user->where("id='".$id."'")->find();
		if($res)
		{
			$resdata=array(
				"id" => $id,
				"is_login" => 1,
				"os"       => 0,
				"deviceid" => 0
			);
			$result=$this->user->save($resdata);
			unset($data);
			$data['code']=1; 
			$data['message']='退出成功！';
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0; 
			$data['message']='用户不存在！';
			outJson($data);
		}
	}
	
	
	

}
?>