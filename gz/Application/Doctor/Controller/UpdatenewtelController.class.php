<?php
// +----------------------------------------------------------------------
// | 更改手机号--输入新的手机号接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class UpdatenewtelController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->sms =D("Sms");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function do_updatenewtel(){
	$id = $this->uid;
	$username = I('post.username');//新的手机号
	$code = I('post.code');
	if(empty($username)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请输入新的手机号码！";
		outJson($data);
	}
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
		$res = array(
				'username' => $username,
			);
	$result = $this->user->where("id = '".$id."'")->save($res);

	if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "更改成功！";
			outJson($data);
		
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "更改失败！";
		outJson($data);
	}
}


	
}
?>