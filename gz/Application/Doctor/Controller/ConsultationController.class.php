<?php
// +----------------------------------------------------------------------
// | 发起会诊接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class ConsultationController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->onlinetreat =D("Onlinetreat");
		$this->onlinetreatuser =D("Onlinetreatuser");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield("id");
	}
	
public	function do_consultation()
	{
		$id = $this->uid;
		$description = I('post.description');
		// $description = '会诊室';
		$img_thumb = I('post.img_thumb','');
		// $img_thumb = '111';
		if(empty($description))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请描述患者症状！";
			outJson($data);
		}
		if(empty($img_thumb))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请上传会诊室头像！";
			outJson($data);
		}


		$res = array(
			'user_id'  => $id,
			'description' => $description,
			'img_thumb' => $img_thumb,
			'status'  => 1,
			'create_time' => time(),
		);
		$result = $this->onlinetreat->add($res);
		$ress = array(
			'online_id' => $result,
			'user_id' => $id,
		);
		
		if($result)
		{
			$results = $this->onlinetreatuser->add($ress);
			unset($data);
			$data['code']= 1;
			$data['message']="发起成功！";
			$data['online_id']=$result;
			outJson($data);
			
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="发起失败！";
			outJson($data);
		}
		
	}
	

}
?>