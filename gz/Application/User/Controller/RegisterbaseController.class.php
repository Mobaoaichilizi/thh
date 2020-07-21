<?php
// +----------------------------------------------------------------------
// | 用户注册基本信息文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class RegisterbaseController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patientmember =D("Patientmember");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');		
	}
	
	//提交注册
	public function do_regbase()
	{
		$id = $this->uid;
		$name = I('post.name');
		$sex = I('post.sex');
		$age = I('post.age');
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入姓名！";
			outJson($data);
		}
		if(empty($sex))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择性别！";
			outJson($data);
		}
		if(empty($age))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入年龄！";
			outJson($data);
		}
		$res=array(
			'name' => $name,
			'sex' => $sex,
			'age' => $age,
		);
		$result=$this->patientmember->where("user_id=".$id)->save($res);
		if($result)
		{
			
			unset($data);
			$data['code']=1;
			$data['message']="保存成功！";
			$data['uid']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="注册失败！";
			outJson($data);
		}
		
	}
	

}
?>