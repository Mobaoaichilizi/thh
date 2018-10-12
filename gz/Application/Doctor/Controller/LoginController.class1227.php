<?php
// +----------------------------------------------------------------------
// | 用户登录接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class LoginController extends DoctorbaseController {
	function _initialize() {
	
		parent::_initialize();
		$this->user =D("User");
		$this->member = D('Member');
		
		
	}
	//验证手机号码是否存在
public	function do_login()
	{
		$username = I('post.username');
		$password = I('post.password');
		$os = I('post.os');
		$deviceid = I('deviceid');
		$verison = I('verison');
		if(empty($username))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入手机号码！";
			outJson($data);
		}
		if(empty($password))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入密码！";
			outJson($data);
		}
		
		$result = $this->user->field("id,password,os,deviceid")->where("username='".$username."' and role=1")->find();
		if($result)
		{
			if($result['password']==sp_password($password))
			{
				$res = array(
					'last_login_time' => time(),
					'deviceid' => $deviceid,
					'os' => $os,

				);

    			// $res['last_login_time']=time();
				$this->user->where("username=".$username)->save($res);
				$results = $this->member->field("name,sex,age,status,img_thumb")->where("user_id ='".$result['id']."'")->find();
				$results['username'] = $username;
				$results['uid'] = md5(md5($result['id']));
				unset($data);
				$data['code']="1";
				$data['message']="登录成功！";
				$data['info'] = $results;
				// $data['uid']=md5(md5($result['id']));
				outJson($data);
			}else
			{
				unset($data);
				$data['code']=0;
				$data['message']="密码错误！";
				outJson($data);
			}
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="用户不存在！";
			outJson($data);
		}
		
	}
	//退出登陆
public function logout()
	{	
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
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
				"is_login" => 0,
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