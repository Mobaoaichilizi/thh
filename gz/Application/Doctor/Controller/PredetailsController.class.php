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
		$this->admin =D("Admin");
		$this->user =D("User");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function pre_details(){
		$uid = $this->uid;
		$id = I('post.id');//处方的ID
		// $id = 82;//处方的ID
		$results = $this->pre->field("setting_id_class,setting_id_type,hrebs_id,patient_id,img_thumb_common,img_thumb_prime,pre_description,pre_bz,dosage_name,admin_pre,total")->where("id = '".$id."'")->find();
		if($results){
			$patient = $this->patient->field('name,sex,age')->where("user_id='".$results['patient_id']."'")->find();
			$result['pname'] = $patient['name'];
			$result['sex'] = $patient['sex'];
			$result['age'] = $patient['age'];
			$result['phone'] =  $this->user->where("id=".$results['patient_id'])->getfield('username');
			$result['doctor'] = $this->member->where("user_id = '".$uid."'")->getfield('name');
			if($results['setting_id_class'] == 44){
				$result['img_thumb_prime'] = $results['img_thumb_prime'];
				$result['img_thumb_common'] = $results['img_thumb_common'];
				$result['pre_bz'] = $results['pre_bz'];
			}else{
				if($results['dosage_name'] == 131){
					$result['dosage_name'] = "原药";
				}else if($results['dosage_name'] == 132){
					$result['dosage_name'] = '汤药代煎';
				}else if($results['dosage_name'] == 133){
					$result['dosage_name'] = '膏方制作';
				}
			}
			$result['setting_id_class'] = $this->setting->where("id = '".$results['setting_id_class']."'")->getfield("title");
			$result['admin_pre'] = $this->admin->where('id='.$results['admin_pre'])->getfield('admin_name');
			
			if((!empty($results['hrebs_id']) || ($results['hrebs_id']!=0)) && $results['setting_id_class'] == '43'){
				$hrebs = $this->hrebs->field('setting_id_type,pre_hrebs_id,setting_id_usage,hrebs_number,hrebs_total')->where("id = '".$results['hrebs_id']."'")->find();

				$result['hrebs_total'] = $hrebs['hrebs_total'];
				$result['setting_id_usage'] = $this->setting->where("id='".$hrebs['setting_id_usage']."'")->getfield("title");
				if(!empty($hrebs['pre_hrebs_id'])){
					$res = $this->pre_hrebs->join("lgq_single_hrebs on lgq_single_hrebs.id = lgq_pre_hrebs.hrebs_name_id")->field('lgq_pre_hrebs.id,lgq_pre_hrebs.hrebs_name_id,lgq_pre_hrebs.hrebs_dosage,lgq_single_hrebs.setting_id_model as setting_id_type')->where("lgq_pre_hrebs.id in (".$hrebs['pre_hrebs_id'].")")->select();
					foreach ($res as $key => $value) {
						$value['hrebs_name_id'] = $this->single_hrebs->where("id='".$value['hrebs_name_id']."'")->getfield('hrebs_name');
						$ress[]=$value;

					}
					if(empty($ress)){
						$result['pre_hrebs_id'] = array();
					}else{
						$result['pre_hrebs_id'] = $ress;
					}
				}else{
					$result['pre_hrebs_id'] = array();
				}

				$result['hrebs_number'] = $hrebs['hrebs_number'];
				$result['pre_instructions'] = $this->hrebs->where('id='.$results['hrebs_id'])->getField('hrebs_bz');
				$result['pre_description'] = $results['pre_description'];
					
				
			}else{
				$result['pre_hrebs_id'] = array();
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