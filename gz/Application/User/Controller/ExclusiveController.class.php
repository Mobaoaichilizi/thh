<?php
// +----------------------------------------------------------------------
// | 专属医生接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class ExclusiveController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->exclusive = D('Exclusive');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
//用户扫一扫
public	function exclusive_info(){
		$id = $this->uid;
		// $doctor_id = $this->user->where("md5(md5(id))='".$_POST['doctor_id']."'")->getField('id');
		$doctor_id = I('post.doctor_id');
		if(empty($doctor_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择医生！";
			outjson($data);
		}
		$count = $this->exclusive->where("patient_id=".$id." and doctor_id=".$doctor_id)->count();
		if($count>0){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "此医生已是你的专属医生！";
			outjson($data);
		}
		$res = array(
			'patient_id' => $id,
			'doctor_id' => $doctor_id,
		);
		$result = $this->exclusive->add($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "添加成功！";
			outjson($data);
		}else{
			unset($data);
			$data['code'] = 01;
			$data['message'] = "添加失败！";
			$data['info'] = array();
			outjson($data);
		}

	}
	public function exclusive_list(){
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		$p=($p-1)*$limit;
		$result = $this->exclusive->where("patient_id=".$id)->order("id desc")->limit($p.",".$limit)->select();
		foreach ($result as $key => $value) {
			$results[] = $this->member->field('user_id,name,img_thumb')->where('user_id='.$value['doctor_id'])->find();
			$results[$key]['doctor_id'] = md5(md5($value['doctor_id']));
		}
		
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "获取列表成功！";
			$data['info'] = $results;
			outjson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无数据！";
			$data['info'] = array();
			outjson($data);
		}
	}
	public function cancel_exclusive(){
		$id = $this->uid;
		// $doctor_id = $this->user->where("md5(md5(id))='".$_POST['doctor_id']."'")->getField('id');
		$doctor_id = I('post.doctor_id');
		if(empty($doctor_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择医生！";
			outjson($data);
		}
		$result = $this->exclusive->where("patient_id=".$id." and doctor_id=".$doctor_id)->delete();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "取消成功！";
			outjson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "取消失败！";
			outjson($data);
		}
	}
	


}
?>