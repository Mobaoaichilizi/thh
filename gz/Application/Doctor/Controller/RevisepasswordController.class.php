<?php
// +----------------------------------------------------------------------
// | 修改密码接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class RevisepasswordController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user   = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
public function getusername(){
	$uid = $this->uid;
	$result = $this->user->where("id='".$uid."'")->getfield("username");
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "获取用户名成功！";
		$data['username'] = $result;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "获取用户名失败！";
		outJson($data);
	}
}	
public	function do_password()
	{
		$id = $this->uid;
		$password = I('post.password');
		$newpassword = I('post.newpassword');
		$newpassworded = I('post.newpassworded');
		if(empty($password))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入原密码！";
			outJson($data);
		}
		if(empty($newpassword))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入新密码！";
			outJson($data);
		}
		if(empty($newpassworded))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请确认新密码！";
			outJson($data);
		}
		if($newpassword!=$newpassworded)
		{
			unset($data);
			$data['code']=0;
			$data['message']="两次密码不同！";
			outJson($data);
		}
		$user=$this->user->where("id='".$id."' and password='".sp_password($password).".")->find();
		if($user)
		{
			$res=array(
			'password' => sp_password($newpassword),
			);
			$result=$this->user->where("id=".$id)->save($res);

			unset($data);
			$data['code']=1;
			$data['message']="修改成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code']=0;
			$data['message']="原始密码错误！";
			outJson($data);
		}
		
		
		
	}
	
}
?>