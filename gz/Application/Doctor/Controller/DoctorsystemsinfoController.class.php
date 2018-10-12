<?php
// +----------------------------------------------------------------------
// | 医生列表消息接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class DoctorsystemsinfoController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->systemsinfo =D("SystemsInfo");
		$this->member =D("Member");
		$this->patient =D("Patientmember");
		$this->user =D("User");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function info_list(){
	$id = $this->uid;
	$p=!empty($_POST['p']) ? $_POST['p'] : 1;
	$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
	$p=($p-1)*$limit;
	$result = $this->systemsinfo->field("id,description,send_uid,type_id,createtime")->where("receive_uid = '".$id."' and type > 1")->limit($p.','.$limit)->order("createtime desc")->select();
	foreach ($result as &$value) {
		$value['send_name'] = $this->patient->where("user_id='".$value['send_uid']."'")->getField('name');
		$value['send_img'] = $this->patient->where("user_id='".$value['send_uid']."'")->getField('img_thumb');
		$value['createtime'] = time_tran($value['createtime']);
	
	}
	

	if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息列表加载成功！";
			$data['info'] = $result;
			outJson($data);
		
	}else{
		unset($data);
		$data['code'] = 1;
		$data['message'] = "暂无消息！";
		$data['info'] = array();
		outJson($data);
	}
}

	
}
?>