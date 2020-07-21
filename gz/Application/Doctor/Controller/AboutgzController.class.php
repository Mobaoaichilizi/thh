<?php
// +----------------------------------------------------------------------
// | 关于广正接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class AboutgzController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->information = D('Information');
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
public function getuserknow(){
	$uid = $this->uid;
	$result = $this->information->field('content')->where("setting_id=79")->find();
	
	if($result){
	    $result['content'] = html_entity_decode($result['content']);
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

public	function fun_introduction()
	{
		$uid = $this->uid;
		$result = $this->information->field('content')->where("setting_id=80")->find();
		
		if($result){
			$result['content'] = html_entity_decode($result['content']);
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