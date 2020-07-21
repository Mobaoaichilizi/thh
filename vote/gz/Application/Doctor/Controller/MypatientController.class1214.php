<?php
// +----------------------------------------------------------------------
// | 我的患者接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class MypatientController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user=D("User");
		$this->patient =D("Patientmember");
		$this->reserve =D("Reserve");
		$this->telephone =D("Telephone");
		$this->graphic =D("graphic");
		$this->video =D("videodia");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
public function patient_diaed(){
	$id = $this->uid;
	$p=!empty($_POST['p']) ? $_POST['p'] : 0;
	$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
	$p=($p-1)*$limit;
	
		$map0['lgq_graphic.status'] = array("eq",2);
		
		$ress['patient'] = $this->patient->join("lgq_graphic on lgq_graphic.patientmember_id = lgq_patientmember.user_id")->field("user_id,lgq_patientmember.img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_graphic.id,type,update_time")->where("lgq_graphic.status=2 and lgq_patientmember.status=1")->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['update_time'] = format_date($this->graphic->where($map0)->getfield('update_time'));
			}	
			
		}
		$res[] = $ress;
	
		$map1['lgq_telephone.status'] = array("eq",2);
		
		$ress['patient'] = $this->patient->join("lgq_telephone on lgq_telephone.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_telephone.id,type,tel_date")->where("lgq_telephone.status=2 and lgq_patientmember.status=1")->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['tel_date'] = format_date($this->telephone->where($map1)->getfield('tel_date'));
			}	
		}
		$res[] = $ress;


		$map2['lgq_reserve.status'] = array("eq",2);
		
		$ress['patient'] = $this->patient->join("lgq_reserve on lgq_reserve.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_reserve.id,type,res_date")->where("lgq_reserve.status=2 and lgq_patientmember.status=1")->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['res_date'] = format_date($this->reserve->where($map2)->getfield('res_date'));
			}	
		}
		$res[] = $ress;

		$map3['lgq_videodia.status'] = array("eq",2);
		
		$ress['patient'] = $this->patient->join("lgq_videodia on lgq_videodia.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_videodia.id,type,video_date")->where("lgq_videodia.status=2 and lgq_patientmember.status=1")->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['video_date'] = format_date($this->video->where($map3)->getfield('video_date'));
			}	
		}
		$res[] = $ress;
		foreach ($res as $key => $value) {
			foreach ($value as $k => $v) {
				foreach ($v as $a => $r) {
					$result[] = $r;
				}
				
			}
		}
	if($limit !== 0){
		$result = array_splice($result,$p,$limit);
	}
	$results['count'] = count($result);
	$results['patient'] = $result;
	if(empty($results['patient'])){
		$results['patient'] = array();
	}
	
	if($results){
		
		unset($data);
		$data['code'] = 1;
		$data['message'] = "加载成功！";
		$data['info'] = $results;
		outJson($data);
	
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "加载失败！";
		$data['info'] = array();
		outJson($data);
	}
	
}

public function patient_dia(){
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$map0['lgq_graphic.status'] = array("eq",1);
		unset($ress);
		$ress['patient'] = $this->patient->join("lgq_graphic on lgq_graphic.patientmember_id = lgq_patientmember.user_id")->field("user_id,lgq_patientmember.img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_graphic.id,type,update_time")->where("lgq_graphic.status=1 and lgq_patientmember.status=1")->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['update_time'] = format_date($this->graphic->where($map0)->getfield('update_time'));
			}	
			
		}
		$res[] = $ress;
	
		$map1['lgq_telephone.status'] = array("eq",1);
		unset($ress);
		$ress['patient'] = $this->patient->join("lgq_telephone on lgq_telephone.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_telephone.id,type,tel_date")->where("lgq_telephone.status=1 and lgq_patientmember.status=1")->select();

		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['tel_date'] = format_date($this->telephone->where($map1)->getfield('tel_date'));
			}	
		}

		$res[] = $ress;


		$map2['lgq_reserve.status'] = array("eq",1);
		unset($ress);
		$ress['patient'] = $this->patient->join("lgq_reserve on lgq_reserve.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_reserve.id,type,res_date")->where($map2)->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['res_date'] = format_date($this->reserve->where($map2)->getfield('res_date'));
			}	
		}

		$res[] = $ress;

		$map3['lgq_videodia.status'] = array("eq",1);
		unset($ress);
		$ress['patient'] = $this->patient->join("lgq_videodia on lgq_videodia.patientmember_id = lgq_patientmember.user_id")->field("user_id,img_thumb,lgq_patientmember.name,lgq_patientmember.phone,lgq_videodia.id,type,video_date")->where($map3)->select();
		foreach($ress as $k=>$v){
			foreach ($v as $key => $value) {
				$ress['patient'][$key]['video_date'] = format_date($this->video->where($map3)->getfield('video_date'));
			}	
		}
		$res[] = $ress;
		foreach ($res as $key => $value) {
			foreach ($value as $k => $v) {
				foreach ($v as $a => $r) {
					$result[] = $r;
				}
				
			}
		}
	if($limit !== 0){
		$result = array_splice($result,$p,$limit);
	}
	$results['count'] = count($result);
	$results['patient'] = $result;
	if(empty($results['patient'])){
		$results['patient'] = array();
	}		
		if($results){
			
				unset($data);
				$data['code'] = 1;
				$data['message'] = "加载成功！";
				$data['info'] = $results;
				outJson($data);
			
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "加载失败！";
			$data['info'] = array();
			outJson($data);
		}
		
	}
}
?>