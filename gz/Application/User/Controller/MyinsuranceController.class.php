<?php
// +----------------------------------------------------------------------
// | 我的医保接口
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MyinsuranceController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->information = D("Information");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//我的医保
	public function myinsurance()
	{
		$uid = $this->uid;
		$result = $this->information->where("setting_id=86")->getfield("content");
		
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "暂无信息！";
			outJson($data);
		}
		
	}
}
?>