<?php
// +----------------------------------------------------------------------
// | 患者档案接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class PatientarchivesController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->patient =D("Patientmember");
		$this->user=M("User");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield('id');
	}
//患者信息
public	function patient_archives()
	{
		$uid = $this->uid;
		$id = I('post.id');
		// $id = '4a0f84dd91471107bf6a1dfce1d62fc0';
		$result = $this->patient->where("md5(md5(user_id))='".$id."' or user_id='".$id."'")->find();
		// dump($this->patient->getlastsql());
		$result['phone']=$this->user->where("id=".$result['user_id'])->getfield('username');
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="此患者不存在";
			$data['info'] = array();
			outJson($data);
		}
		
	}

}
?>