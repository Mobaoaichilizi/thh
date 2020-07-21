<?php
// +----------------------------------------------------------------------
// | 传统处方详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class PredetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->pre =D("Pre");
		$this->patient =D("Patientmember");
		$this->member =D("Member");
		$this->hrebs =D("Hrebs");
		$this->pre_hrebs =D("PreHrebs");
		$this->single_hrebs =D("SingleHrebs");
		$this->setting =D("setting");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function pre_details(){
		$uid = $this->uid;
		// $uid = 3;
		$id = I('post.id');//处方的ID
		$id = 13;
		$results = $this->pre->field("setting_id_class,setting_id_type,hrebs_id,patient_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz")->where("id = '".$id."'")->find();
		if($results){
			$patient = $this->patient->field('name,sex,age,phone')->where("user_id='".$results['patient_id']."'")->find();
			$result['pname'] = $patient['name'];
			$result['sex'] = $patient['sex'];
			$result['age'] = $patient['age'];
			$result['phone'] = $patient['phone'];
			$result['doctor'] = $this->member->where("user_id = '".$uid."'")->getfield('name');
			$result['setting_id_class'] = $this->setting->where("id = '".$results['setting_id_class']."'")->getfield("title");
			$result['img_thumb_prime'] = $results['img_thumb_prime'];
			$result['img_thumb_common'] = $results['img_thumb_common'];
			$result['pre_description'] = $results['pre_description'];
			$result['pre_bz'] = $results['pre_bz'];
			if(!empty($results['hrebs_id']) || ($results['hrebs_id']!=0)){
				$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number')->where("id = '".$results['hrebs_id']."'")->find();
				// $result['setting_id_type'] = $this->setting->where("id='".$hrebs['setting_id_type']."'")->getfield("title");
				$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
					$res = $this->pre_hrebs->where("id in (".$hrebs['pre_hrebs_id'].")")->select();
				$result['hrebs_number'] = $hrebs['hrebs_number'];
				dump($res);
					foreach ($res as $key => $value) {
						$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
						$value['setting_id_type'] = $this->single_hrebs->join("lgq_setting on lgq_setting.id=lgq_single_hrebs.setting_id_model")->where("lgq_single_hrebs.id='".$value['hrebs_name_id']."'")->getfield("title");
						$ress[]=$value;

					}
					$result['pre_hrebs_id'] = $ress;
				
			}
			
			unset($data);
			$data['code'] = 1;
			$data['message'] = "处方加载成功！";
			$data['info'] = $result;
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