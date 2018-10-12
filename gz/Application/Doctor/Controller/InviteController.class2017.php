<?php
// +----------------------------------------------------------------------
// | 邀请好友接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class InviteController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");//用户信息表
		$this->doctor = D('Member');
		$this->patient = D('Patientmember');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
public	function do_invite()
	{
		$id = $this->uid;
		$result = $this->user->where('id='.$id)->getfield('share_number');
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}
	}
public function invite_doctor(){
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=$p*$limit;
		$share_number = $this->user->where("id='".$uid."' and role = 1")->getfield("share_number");
		if(empty($share_number)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$res = $this->user->field('id,createtime')->where("inviter_share_number = '".$share_number."' and role = 1")->limit($p.",".$limit)->select();
		if(empty($res)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "暂无数据！";
			outJson($data);
		}
		foreach ($res as $k=>$vo) {
			$results[$k]['name'] = $this->doctor->where("user_id='".$vo['id']."'")->getfield("name");
			$results[$k]['img_thumb'] = $this->doctor->where("user_id='".$vo['id']."'")->getfield("img_thumb");
			$results[$k]['id'] = $vo['id'];
			$results[$k]['createtime'] = $vo['createtime'];
		}
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "数据加载成功！";
			$data['info'] = $results;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}

	}
public function invite_patient(){
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=$p*$limit;
		$share_number = $this->user->where("id='".$uid."' and role = 1")->getfield("share_number");
		if(empty($share_number)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$res = $this->user->field('id,createtime')->where("inviter_share_number = '".$share_number."' and role = 2")->limit($p.",".$limit)->select();
		if(empty($res)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "暂无数据！";
			outJson($data);
		}
		foreach ($res as $vo) {
			$results[$k]['name'] = $this->patient->where("user_id='".$vo['id']."'")->getfield("name");
			$results[$k]['img_thumb'] = $this->patient->where("user_id='".$vo['id']."'")->getfield("img_thumb");
			$results[$k]['id'] = $vo['id'];
			$results[$k]['createtime'] = $vo['createtime'];
		}
		if($results){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "数据加载成功！";
			$data['info'] = $results;
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