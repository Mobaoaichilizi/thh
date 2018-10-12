<?php
// +----------------------------------------------------------------------
// | 个人资料更改手机号接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class UpdatetelController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->sms =D("Sms");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function do_updatetel(){
	$id = $this->uid;
	$code = I('post.code');
	if(empty($code)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请输入验证码！";
		outJson($data);
	}
	$nowtime=time()-3600;  //一个小时之内有效
		$count = $this->sms->where("phone='".$username."' and type=1 and code='".$code."' and createtime > ".$nowtime)->count();
		if($count <= 0)
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入正确的验证码！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="验证码正确";
			outJson($data);
		}
}
public function getusername(){
	$id = $this->uid;
	// $id = 1;
	$result = $this->user->where("id = '".$id."'")->getfield('username');
	if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "获取成功！";
			$data['info'] = $result;
			outJson($data);
		
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "获取失败！";
		outJson($data);
	}
}

	
}
?>