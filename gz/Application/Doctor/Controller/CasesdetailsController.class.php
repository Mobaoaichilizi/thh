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
class CasesdetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->onlinetreat =D("Onlinetreat");
		$this->onlinetreatuser =D("Onlinetreatuser");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
public	function cases_details()
	{
		$uid = $this->uid;
		$id = I('post.id');//会诊室的id
		// $id = 3;
		$result = $this->onlinetreat->field("img_thumb,description")->where("id='".$id."'")->find();
		if($result)
		{
			unset($data);
			$data['code']= 1;
			$data['message']="加载成功！";
			$data['info']=$result;
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