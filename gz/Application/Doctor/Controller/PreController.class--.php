<?php
// +----------------------------------------------------------------------
// | 处方管理接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class PreController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->pre =D("Pre");
		$this->member =D("Member");
		$this->patientmember =D("Patientmember");
		$this->hrebs =D("Hrebs");
		$this->single_hrebs =D("SingleHrebs");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
//上传电子处方
public function upload_elepre(){
	$id = $this->uid;
	$patient_id = I('post.user_id');
	// $patient_id = 2;
	$setting_id_type = I('post.setting_id_type');
	// $setting_id_type = 47;
	$pre_description = I('post.description');
	// $pre_description = '病情反反复复要复查';
	
	if(empty($patient_id)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择患者！";
		outJson($data);
	}
	if(empty($setting_id_type)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择处方种类！";
		outJson($data);
	}
	if(empty($pre_description)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请填写病例诊断！";
		outJson($data);
	}
	$res = array(
		'setting_id_class' => 43,
		'patient_id' => $patient_id,
		'doctor_id' => $id,
		'pre_description' => $pre_description,
		'setting_id_type' => $setting_id_type,
		// 'hrebs_id' => $hrebs_id,
		'create_time' => time(),
	);
	$results = $this->pre->add($res);
	$result['doctor'] = $this->member->field('name')->where("user_id='".$id."'")->find();
	$result['patient'] = $this->patientmember->field('name,sex,age,phone')->where("user_id='".$patient_id."'")->find();
	$result['pre_id'] = $results;
	// $result['hrebs_price'] = $this->hrebs->field('hrebs_total')->where("id='".$hrebs_id."'")->find();
	$result['pre_id'] = $results;
	if($results){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "处方上传成功！";
		$data['info'] = $result;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "处方上传失败！";
		outJson($data);
	}

}
//上传传统处方
public function upload_trapre(){
	$id = $this->uid;
	$patient_id = I('post.user_id');
	$img_thumb_common = I('post.img_thumb_common','');
	$img_thumb_prime = I('post.img_thumb_prime','');
	$pre_bz = I('post.pre_bz','');
	if(empty($patient_id)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择患者！";
		outJson($data);
	}
	if(empty($img_thumb_common) && empty($img_thumb_prime)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请上传处方！";
		outJson($data);
	}
	$res = array(
		'patient_id' => $patient_id,
		'doctor_id' => $id,
		'setting_id_class' => 44,
		'img_thumb_common' => $img_thumb_common,
		'img_thumb_prime' => $img_thumb_prime,
		'pre_bz' => $pre_bz,
		'create_time' => time(),
	);
	$results = $this->pre->add($res);
	$result['doctor'] = $this->member->field('name')->where("user_id='".$id."'")->find();
	$result['patient'] = $this->patientmember->field('name,sex,age,phone')->where("user_id='".$patient_id."'")->find();
	$result['pre_id'] = $results;
	if($results){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "处方上传成功！";
		$data['info'] = $results;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "处方上传失败！";
		outJson($data);
	}

}
//处方展示
public function pre_list(){
	$id = $this->uid;
	$result = $this->pre->field('id,patient_id,create_time')->where("doctor_id = '".$id."' and setting_id_class = 43")->select();
	$results = $this->pre->field('id,patient_id,create_time')->where("doctor_id = '".$id."' and setting_id_class = 44")->select();
	foreach ($result as $k=>$vo){
		$result[$k]['electron'] = $this->patientmember->field('img_thumb,name')->where("user_id='".$vo['patient_id']."'")->find();
		 
	}
	foreach ($results as $k=>$vo) {
		$results[$k]['tradition'] = $this->patientmember->field('img_thumb,name')->where("user_id='".$vo['patient_id']."'")->find();
	}
	if($result){
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['electron'] = $result;
			$data['tradition'] = $results;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['electron'] = $result;
			outJson($data);
		}
		
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "处方加载失败！";
		outJson($data);
	}
}
public function hrebs_list()
	{
		$uid = $this->uid;
		$firstletter = I('post.firstletter');
		$setting_id_type = I('post.setting_id_type');

		$result = $this->single_hrebs->field("hrebs_name,unit_price,id,setting_id_model")->where("firstletter='".$firstletter."' and setting_id_model=".$setting_id_type)->select();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "药材加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 2;
			$data['message'] = "暂无数据！";
			outJson($data);
		}
	}
}
?>