<?php
// +----------------------------------------------------------------------
// | 上传处方接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class UploadpreController extends DoctorbaseController {
	function _initialize() {
		$token=$_REQUEST['token'];
		accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->pre =D("Pre");//处方
		$this->personaddress =D("Personaddress");//患者地址
		$this->hrebs =D("hrebs");//药材清单
	}
	//手动添加患者信息
public	function do_patientinfo(){
		$person = I('post.person');
		$sex = I('post.sex');
		$age = I('post.age');
		$phone = I('post.phone');
		$address = I('post.address');
		if(empty($person)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入患者姓名！";
			outJson($data);
		}
		if(empty($sex)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择患者性别！";
			outJson($data);
		}
		if(empty($age)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入患者年龄！";
			outJson($data);
		}
		if(empty($phone)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入患者电话！";
			outJson($data);
		}
		if(empty($address)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入详细的收货地址！";
			outJson($data);
		}
		$res = array(
			'person' => $person,
			'sex'    => $sex,
			'age'    => $age,
			'phone'  => $phone,
			'address'=> $address,
		);
		$result = $this->personaddress->add($res);
		if($result){
			return $result;//手动输入的患者信息（id）
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "信息保存失败！";
			outJson($data);
		}

	}
	//药材清单
public	function do_hrebs(){
		$setting_id_type = I('post.setting_id_type');
		$single_hrebs_id = I('post.single_hrebs_id');
		$setting_id_model = I('post.setting_id_model');
		$setting_id_usage = I('post.setting_id_usage');
		$hrebs_number = I('post.hrebs_number');
		$hrebs_bz = I('post.hrebs_bz');
		if(empty($setting_id_type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材规格";
			outJson($data);
		}
		if(empty($single_hrebs_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请添加药材";
			outJson($data);
		}
		if(empty($setting_id_model)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材剂型";
			outJson($data);
		}
		if(empty($setting_id_usage)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材用法";
			outJson($data);
		}
		if(empty($hrebs_number)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材副数";
			outJson($data);
		}
		$price = $this->hrebs->field('price')->where('id in'.$hrebs_id)->select();
		$total = 0;
		foreach ($price as $vo) {
			$total += $vo;
		}
		$hrebs_total = $hrebs_number * $total;
		$res = array(
			'setting_id_type'  => $setting_id_type,
			'single_hrebs_id'  => $single_hrebs_id,
			'setting_id_model' => $setting_id_model,
			'setting_id_usage' => $setting_id_usage,
			'hrebs_number'     => $hrebs_number,
			'hrebs_bz'         => $hrebs_bz,
			'hrebs_total'      => $hrebs_total,
		);
		$result = $this->hrebs->save($res);
		if($result){
			// return $result; //药材清单（id）
			unset($data);
			$data['code'] = 1;
			$data['message'] = "保存成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "保存失败！";
			outJson($data);
		}

	}
	//上传处方
public	function do_uploadpre()
	{
		$id = I('post.id');
		$patient = I('post.patient','');
		// $patient_id = $this->do_patientinfo();
		$patient_id = I('post.personaddress_id','');
		
		$setting_id_class = I('post.setting_id_class','');
		$setting_id_type = I('post.setting_id_type','');
		// $hrebs_id = $this->do_hrebs();
		$hrebs_id = I('post.hrebs_id');
		$pre_description = I('post.pre_description','');
		$pre_bz = I('post.pre_bz','');
		$img_thumb_common = I('img_thumb_common','');
		$img_thumb_prime = I('img_thumb_prime','');
		if($setting_id_class == 44){
			if(empty($img_thumb_common) && empty($img_thumb_prime)){
				unset($data);
				$data['code'] = 0;
				$data['message'] = "请上传处方图片！";
				outJson($data);
			}
		}
		if(empty($patient) && empty($patient_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择或输入患者信息！";
			outJson($data);
		}
		if($setting_id_class == 43){
			if(empty($pre_type))
			{
				unset($data);
				$data['code']=0;
				$data['message']="请选择处方种类！";
				outJson($data);
			}
			if(empty($pre_hrebs))
			{
				unset($data);
				$data['code']=0;
				$data['message']="请选择药材清单！";
				outJson($data);
			}
			
		}
		if(empty($pre_description))
			{
				unset($data);
				$data['code']=0;
				$data['message']="请填写病情诊断！";
				outJson($data);
			}

		$res = array(
			'doctor_id' => $id,
			'patient_id'=> $patient,
			'personaddress_id' => $patient_id,
			'setting_id_class' => $setting_id_class,
			'setting_id_type' => $setting_id_type,
			'hrebs_id' => $hrebs_id,
			'pre_description' => $pre_description,
			'pre_bz' => $pre_bz,
			'create_time' => time(),
		);
		$result = $this->pre->add($res);
		
		if($result)
		{
			unset($data);
			$data['code']="1";
			$data['message']="上传处方成功！";
			$data['info']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="上传处方失败！";
			outJson($data);
		}
		
		
	}
	

}
?>