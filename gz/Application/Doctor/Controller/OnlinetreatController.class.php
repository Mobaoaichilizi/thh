<?php
// +----------------------------------------------------------------------
// | 在线会诊管理接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class OnlinetreatController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member = D('Member');
		$this->onlinetreat =D("Onlinetreat");
		$this->onlinetreatuser =D("Onlinetreatuser");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
//会诊室信息
public	function onlinetreat_info()
	{
		$uid = $this->uid;
		$id = I('post.id');
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择会诊室！";
			outJson($data);
		}
		$result = $this->onlinetreatuser->field('online_id,join_user')->where("online_id='".$id."'")->find();
		$result['description'] = $this->onlinetreat->field('description')->where("id='".$id."'")->find();
		$result['user'] = $this->member->field('name,img_thumb')->where("user_id='".$uid."'")->find();
		$result['join_user'] = $this->member->field('name,img_thumb')->where("id in (".$result['join_user'].")")->select();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无会诊信息！";
			$data['info']=array();
			outJson($data);
		}
		
	}
//结束会诊
public function exit_onlinetreat(){
	$uid = $this->uid;
	$id = I('post.id');
	$res = array(
		'status' => 2,
	);
	$result = $this->onlinetreat->where("id='".$id."' and status =  1")->save($res);
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "退出成功！";
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "退出失败！";
		outJson($data);
	}
}
//移除/添加成员
public function update_joinuser(){
	$uid =$this->uid;
	$id =I('post.id');
	$join_user = I('join_user');
	$res = array(
		'join_user' => $join_user,
	);
	$result = $this->onlinetreatuser->where("online_id = '".$id."' and status = 1")->save($res);
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "修改成功！";
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "修改失败！";
		outJson($data);
	}
}
//添加成员的医生列表
public function adduser_list(){
	$uid =$this->uid;
	$id =I('post.id');
	// $id =3;
	if(empty($id)){
		unset($data);
		$data['code'] = 0;
		$data['message'] ="数据错误！";
		outJson($data);

	}
	// $id =3;

	$join_users = $this->onlinetreatuser->join('lgq_onlinetreat on lgq_onlinetreat.id = lgq_onlinetreatuser.online_id')->where("online_id = ".$id." and status = 1")->find();
	if(!empty($join_users)){
		if(!empty($join_users['join_user'])){

			$doctorlist = $this->member->field('name,img_thumb')->where("user_id not in (".$join_users['join_user'].") and status = 2")->select();
		}else{
			$doctorlist = $this->member->field('name,img_thumb')->where("user_id != ".$join_users['user_id']." and status = 2")->select();
		
		}
	}
	
	
	if($doctorlist){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "加载成功！";
		$data['info'] = $doctorlist;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "加载失败！";
		outJson($data);
	}
}

}
?>