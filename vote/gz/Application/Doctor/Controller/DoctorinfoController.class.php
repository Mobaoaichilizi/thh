<?php
// +----------------------------------------------------------------------
// | 医生个人资料接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class DoctorinfoController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member =D("Member");
		$this->user =D("User");
		$this->score = D('ScoreList');
		$this->hospital = D('Hospital');
		$this->department = D('Department');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function doctor_info(){
	$id = $this->uid;
	$result = $this->member->join('lgq_user on lgq_user.id = lgq_member.user_id')->field("username,name,img_thumb,sex,age,card,department_id,hospital_id,certificate,othercert,card_correct,card_opposite,balance,status,professional")->where("user_id = '".$id."'")->find();
	$result['hospital'] = $this->hospital->where("id='".$result['hospital_id']."'")->getfield('name');
	if($result['hospital'] == NULL){
		$result['hospital'] = '';
	}
	$result['department'] = $this->department->where("id='".$result['department_id']."'")->getfield('name');
	if($result['department'] == NULL){
		$result['department'] = '';
	}
	if(!empty($result['othercert'])){
		$result['othercert'] = explode(",", $result['othercert']);
	}

	$result['level'] = $this->user->where("id=".$id)->getfield("member_level");
	$due_time = $this->user->where("id=".$id)->getfield("due_time");

	$t = $due_time-time();
	if($t > 0){
		$result['days_remaining'] = ceil($t/86400);
	}else{
		$result['days_remaining'] = 0;
	}
	

	$result['coupons_number'] = $this->user->where("id=".$id)->getfield("coupons");
	$where['setting_id'] = array('not in','166,207');
	$where['user_id'] = array('eq',$id);
	$result['invite_number'] = $this->score->where($where)->count();
	
	if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "资料加载成功！";
			$data['info'] = $result;
			outJson($data);
		
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "资料加载失败！";
		outJson($data);
	}
}
public function update_img(){
	$uid = $this->uid;
	$img_thumb = I('post.img_thumb');
	if(empty($img_thumb)){
		unset($data);
		$data['code'] = 0;
		$data['message'] = "请选择照片！";
		outJson($data);
	}
	$res = array(
		'img_thumb' => $img_thumb,
	);
	$result = $this->member->where("user_id=".$uid)->save($res);
	unset($data);
	$data['code'] = 1;
	$data['message'] = "保存成功！";
	outJson($data);
}

	
}
?>