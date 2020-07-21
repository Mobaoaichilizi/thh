<?php
// +----------------------------------------------------------------------
// | 用户登录接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class LoginController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->patientmember =D("Patientmember");	
	}
	//用户登录
	public function do_login()
	{
		$username = I('post.username');
		$password = I('post.password');
		$os = I('post.os');
		$deviceid = I('post.deviceid');
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
		$result = $this->user->field("id,password")->where("username='".$username."' and role=2")->find();
		if($result)
		{
			if($result['password']==sp_password($password))
			{
    			$res['last_login_time']=time();
				$res['os']=$os;
				$res['deviceid']=$deviceid;
				$res['is_login']=0;
				$this->user->where("id=".$result['id'])->save($res);
				$rest=$this->patientmember->field("name,age,sex,status,img_thumb")->where("user_id=".$result['id'])->find();
				if($rest)
				{
					$resc=$this->user->where("id=".$result['id'])->find();
					$this->user->where("id!=".$resc['id']." and deviceid='".$deviceid."'")->save(array('is_login'=>1,'os' => '','deviceid' => ''));
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
					$data['code']="1";
					$data['message']="登录成功！";
					$data['info']=$rest;
					outJson($data);
				}	
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
	

}
?>