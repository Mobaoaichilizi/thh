<?php
// +----------------------------------------------------------------------
// | 关于广正接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class AboutgzController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		parent::_initialize();
		$this->information = D('Information');
		$this->user = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
public function getuserknow(){
	$uid = $this->uid;
	$result = html_entity_decode($this->information->where("setting_id=79")->getfield("content"));
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "信息加载成功！";
		$data['info'] = $result;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "信息加载失败！";
		outJson($data);
	}
}	

public	function fun_introduction()
	{
		$uid = $this->uid;
		$result = html_entity_decode($this->information->where("setting_id=80")->getfield("content"));
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "信息加载失败！";
			outJson($data);
		}
	}
	

public function getusername(){
	$uid = $this->uid;
	if(empty($uid)){
		unset($data);
		$data['code']=0;
		$data['message']="请先登录！";
		outJson($data);	
	}
	$result = $this->user->where("id='".$uid."' and role = 2")->getfield("username");
	if($result){
		unset($data);
		$data['code'] = 1;
		$data['message'] = "获取用户名成功！";
		$data['username'] = $result;
		outJson($data);
	}else{
		unset($data);
		$data['code'] = 0;
		$data['message'] = "获取用户名失败！";
		outJson($data);
	}
}	

public	function do_password()
	{
		$id = $this->uid;
		if(empty($id)){
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$password = I('post.password');
		$newpassword = I('post.newpassword');
		$newpassworded = I('post.newpassworded');
		if(empty($password))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入原密码！";
			outJson($data);
		}
		if(empty($newpassword))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入新密码！";
			outJson($data);
		}
		if(empty($newpassworded))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请确认新密码！";
			outJson($data);
		}
		if($newpassword!=$newpassworded)
		{
			unset($data);
			$data['code']=0;
			$data['message']="两次密码不同！";
			outJson($data);
		}
		$user=$this->user->where("id='".$id."' and role = 2 and password='".sp_password($password)."'")->find();
		if(user)
		{
			$res=array(
				'password' => sp_password($newpassword),
			);
			$result=$this->user->where("id=".$id)->save($res);
			
			unset($data);
			$data['code']=1;
			$data['message']="修改成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code']=0;
			$data['message']="原密码错误！";
			outJson($data);
		}	
	}
	public function version_update()
	{
		$version=I('post.version');
		if(empty($version))
		{
			unset($data);
			$data['code']=0;
			$data['message']="版本号不能为空！";
			outJson($data);
		}
		$res=M("Version")->where("version='".$version."'")->order("createtime desc")->find();
		if(!$res){
		   $res_t=M("Version")->order("createtime desc")->find();
		   $result['code']=1; 
		   $result['message']='请更新版本';
		   $result['info']=array(
			'version' => $res_t['version'],
			'ver_desc' => $res_t['ver_desc'],
			'ver_url' => $res_t['ver_url'],
			'is_mandatory' => $res_t['is_mandatory'],
		   );
		   outJson($result);
		}else{
		   $result['code']=1; 
		   $result['message']='暂无更新版本';
		   $result['info']=array();
		   outJson($result);
		}
	}
}
?>