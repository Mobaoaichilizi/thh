<?php
// +----------------------------------------------------------------------
// | 会诊室的医生列表接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class DoctorlistController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member =D("Member");//医生信息表
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//获取专家列表
	public function doctor_list()
	{
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->member->field('user_id,name,img_thumb')->where("user_id!='".$id."' and status = 1")->limit($p.",".$limit)->select();
		if($result){
			foreach($result as &$row)
			{
				$row['encryption_id']=md5(md5($row['user_id']));
			}
			unset($data);
			$data['code'] = 1;
			$data['message'] = "列表加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无数据";
			$data['info'] = array();
			outJson($data);
		}
	}
}
?>