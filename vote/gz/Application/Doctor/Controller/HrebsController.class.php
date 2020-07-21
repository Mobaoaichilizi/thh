<?php
// +----------------------------------------------------------------------
// | 药材清单接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class HrebsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->hrebs =D('Hrebs');//药材清单
		$this->pre_hrebs =D("PreHrebs");//所需单个药材
		$this->single_hrebs =D("SingleHrebs");//药材库
		$this->setting =D("Setting");
		$this->pre =D("Pre");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//药材清单
public	function do_hrebs(){
		$uid = $this->uid;
		$pre_id = I('post.pre_id');
		// $pre_id = 12;
		$setting_id_type = I('post.setting_id_type');
		// $setting_id_type = 50;// 标准饮片
		$pre_hrebs_id = I('post.pre_hrebs_id');
		$setting_id_usage = I('post.setting_id_usage');
		// $setting_id_usage = 70;
		$hrebs_number = I('post.hrebs_number');
		// $hrebs_number = 1;
		$hrebs_total = I('post.hrebs_total');
		// $hrebs_total = 100;
		$hrebs_bz = I('post.hrebs_bz','');
		// $hrebs_bz = '谨遵医嘱';
		if(empty($pre_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		if(empty($setting_id_type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材规格！";
			outJson($data);
		}
		if(empty($pre_hrebs_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请添加药材！";
			outJson($data);
		}
		if(empty($setting_id_usage)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材用法！";
			outJson($data);
		}
		if(empty($hrebs_number)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择药材副数！";
			outJson($data);
		}
		$de_json = json_decode($pre_hrebs_id,TRUE);
	    $count_json = count($de_json);
        for ($i = 0; $i < $count_json; $i++)
           {
                $data_id = $de_json[$i]['id'];
                $data_num = $de_json[$i]['drug_num'];
				$ress = array(
								"hrebs_name_id" => $data_id,
								"hrebs_dosage"  => $data_num,
							);
				$hrebs = $this->pre_hrebs->add($ress);
				$arr[] = $hrebs;
            }
	    

		
		$hrebs_id = implode(",", $arr);dump($hrebs_id);

		$res = array(
			'setting_id_type'  => $setting_id_type,
			'pre_hrebs_id'  => $hrebs_id,
			'setting_id_usage' => $setting_id_usage,
			'hrebs_number'     => $hrebs_number,
			'hrebs_bz'         => $hrebs_bz,
			'hrebs_total'      => $hrebs_total,
		);
		$result = $this->hrebs->add($res);
		$ress = array(
			'hrebs_id' => $result,
			'id'       => $pre_id,
		);
		$results = $this->pre->save($ress);
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "上传成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "上传失败！";
			outJson($data);
		}

	}
//药材库
public function singlehrebs_list(){
	$id = $this->uid;
	$result = $this->setting->field('id,title,englishname')->where('parentid=49')->select();
	$results['type'] = $this->setting->where('parentid=49')->getfield('title',true);
	foreach ($result as $key=>$vo) {
		$results["".$vo['englishname'].""] = $this->single_hrebs->where("setting_id_model='".$vo['id']."'")->select();
	}
	if($results){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "药材加载成功！";
		$data['info'] = $results;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "药材加载失败！";
		outJson($data);
	}
	 
}
//添加药材
public	function add_hrebs(){
		$uid = $this->uid;
		$id = I('post.id');
		$hrebs_dosage = I('post.hrebs_dosage');
		$hrebs_dosage = 6;
		if(empty($hrebs_dosage)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入药材剂量！";
			outJson($data);
		}
		$hrebs = $this->single_hrebs->join("lgq_setting on lgq_setting.id=lgq_single_hrebs.setting_id_model")->field('hrebs_name,unit_price,title')->where("lgq_single_hrebs.id='".$id."'")->find();
		$hrebs['hrebs_dosage'] = $hrebs_dosage;
	 	$res = array(
	 		'hrebs_name_id' => $id,
	 		'hrebs_dosage' => $hrebs_dosage,
	 	);
	 	$result = $this->pre_hrebs->add($res);
	 	$hrebs['pre_hrebs_id'] = $result;
	 	if($result){
	 		unset($data);
	 		$data['code'] = 1;
	 		$data['message'] = "添加药材成功！";
	 		$data['info'] = $hrebs;
	 		outJson($data);
	 	}else{
	 		unset($data);
	 		$data['code'] = 0;
	 		$data['message'] = "添加药材失败！";
	 		outJson($data);
	 	}
	}
}
?>