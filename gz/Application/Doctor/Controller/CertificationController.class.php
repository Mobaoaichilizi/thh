<?php
// +----------------------------------------------------------------------
// | 实名认证基本信息接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class CertificationController extends DoctorbaseController {
		function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member =D("Member");
		$this->user =D("User");
		$this->hospital =D("Hospital");
		$this->department =D("Department");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function do_certone(){
		$id = $this->uid;
		$name = I('post.name');
		$sex = I('post.sex');
		$age = I('post.age');
		$card = I('post.card');
		$hospital = I('post.hospital');
		$department = I('post.department');
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写真实姓名！";
			outJson($data);
		}
		if(empty($sex))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写性别！";
			outJson($data);
		}
		if(empty($card))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写身份证号码！";
			outJson($data);
		}
		if(empty($hospital))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写在职医院！";
			outJson($data);
		}
		if(empty($department))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写所在科室！";
			outJson($data);
		}
		if(!empty($hospital)){
			$result = $this->hospital->where("name='".$hospital."'")->getfield("id");
			if(empty($result)){
				$res = array(
					'name' => $hospital,
				);
				$hospital_id = $this->hospital->add($res);
			}else{
				$hospital_id = $result;
			}
		}

		if(!empty($department)){
			$result = $this->department->where("name='".$department."'")->getfield("id");
			if(empty($result)){
				$res = array(
					'name' => $department,
				);
				$department_id = $this->department->add($res);
			}else{
				$department_id = $result;
			}
		}
		$res=array( 
					'name' => $name,
					'sex' => $sex,
					'card' => $card,
					'hospital_id' => $hospital_id,
					'department_id' => $department_id,
				);
		$result=$this->member->where("user_id='".$id."'")->save($res);

		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="更新成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="实名认证失败！";
			outJson($data);
		}
			
	}
public function do_certtwo(){
		$id = $this->uid;
		$name = I('post.name');
		$sex = I('post.sex');
		$age = I('post.age');
		$card = I('post.card');
		$hospital = I('post.hospital');
		$department = I('post.department');
		$card_correct = I('post.card_correct');
		$card_opposite = I('post.card_opposite');
		if(empty($name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写真实姓名！";
			outJson($data);
		}
		if(empty($sex))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写性别！";
			outJson($data);
		}
		if(empty($card))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写身份证号码！";
			outJson($data);
		}
		if(empty($hospital))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写在职医院！";
			outJson($data);
		}
		if(empty($department))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请填写所在科室！";
			outJson($data);
		}
		if(empty($card_correct)){
			unset($data);
			$data['code'] = 0;
			$data['message']="请上传身份证正面！";
			outJson($data);
		}
		if(empty($card_opposite)){
			unset($data);
			$data['code'] = 0;
			$data['message']="请上传身份证反面！";
			outJson($data);
		}
		$res = array(
			"card_correct" => $card_correct,
			"card_opposite" => $card_opposite,
		);
		$result = $this->member->where('user_id='.$id)->save($res);
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="更新成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="实名认证失败！";
			outJson($data);
		}
	}
public	function do_certlast(){
		$id = $this->uid;
		$name = I('post.name');
		$sex = I('post.sex');
		$age = I('post.age');
		$card = I('post.card');
		$hospital_id = I('post.hospital_id');
		$department_id = I('post.department_id');
		$card_correct = I('post.card_correct');
		$card_opposite = I('post.card_opposite');
		$certificate = I('post.certificate');
		$othercert = I('post.othercert');
		
		$res = array(
			"certificate" => $certificate,
			"othercert" => $othercert,
			"card_correct" => $card_correct,
			"card_opposite" => $card_opposite,
			'name' => $name,
			'sex' => $sex,
			'card' => $card,
			"age"  => $age,
			'hospital_id' => $hospital_id,
			'department_id' => $department_id,
			'status'  => 2,
		);
		$result = $this->member->where('user_id='.$id)->save($res);
		$results = $this->member->field("name,sex,age,status,img_thumb")->where("user_id ='".$id."'")->find();
		$username = $this->user->where("id=".$id)->getField("username");
		$results['username'] = $username;
		$results['uid'] = md5(md5($id));
		if($result)
		{
			
			unset($data);
			$data['code']=1;
			$data['message']="更新成功！";
			$data['info'] = $results;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="实名认证失败！";
			outJson($data);
		}
	}

}
?>