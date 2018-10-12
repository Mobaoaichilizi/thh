<?php
// +----------------------------------------------------------------------
// | 上传处方的患者列表接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class PatientlistController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->patientmember =D("Patientmember");//患者信息表
		$this->graphic = D('Graphic');//图文咨询表
		$this->telephone = D('Telephone');//电话咨询表
		$this->reserve = D('Reserve');//预约就诊表
		$this->video = D('Videodia');//视频诊疗表
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//获取患者列表
public	function patient_list()
	{
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$where['member_id'] = array("eq",$id);
		$result1 = $this->graphic->where($where)->getfield('patientmember_id',true);
		foreach ($result1 as $key => $value) {
			$result = $this->patientmember->field('user_id,name,img_thumb,sex,age,phone')->where("user_id = ".$value." and status=1")->order("id desc")->find();
			$ress[] = $result;
		}
		
		$result2 = $this->telephone->where($where)->getfield('patientmember_id',true);
		foreach ($result2 as $key => $value) {
			$result = $this->patientmember->field('user_id,name,img_thumb,sex,age,phone')->where("user_id = ".$value." and status=1")->order("id desc")->find();
			$ress[] = $result;
		}
		
		$result3 = $this->reserve->where($where)->getfield('patientmember_id',true);
		foreach ($result2 as $key => $value) {
			$result = $this->patientmember->field('user_id,name,img_thumb,sex,age,phone')->where("user_id = ".$value." and status=1")->order("id desc")->find();
			$ress[] = $result;
		}
		$result4 = $this->video->where($where)->getfield('patientmember_id',true);
		foreach ($result2 as $key => $value) {
			$result = $this->patientmember->field('user_id,name,img_thumb,sex,age,phone')->where("user_id = ".$value." and status=1")->order("id desc")->find();
			$ress[] = $result;
		}
		
		$ress = array_unique_fb($ress);
		if($limit !== 0){
			$ress = array_splice($ress,$p,$limit);
		}
		
		if($ress){

			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $ress;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}
	}

}
?>