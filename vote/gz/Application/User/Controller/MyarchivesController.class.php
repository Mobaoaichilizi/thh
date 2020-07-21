<?php
// +----------------------------------------------------------------------
// | 我的档案管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MyarchivesController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patientmember =D("Patientmember");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');	
	}
	//个人信息展示
	public function myarchivesinfo()
	{
		$uid=$this->uid;
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$list=$this->patientmember->field("id,name,img_thumb,sex,age,symptoms_desc,taboo_that,other_disease")->where("user_id=".$uid)->find();
		if($list)
		{
			$list['img_thumb']=$this->host.$list['img_thumb'];
			$list['phone']=$this->user->where("id=".$uid)->getField('username');
			unset($data);
			$data['code']=1;
			$data['message']="获取信息成功！";
			$data['info']=$list;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="暂无信息";
			outJson($data);	
		}
		
	}
	
	//编辑个人信息
	public function myarchives_edit()
	{
		$uid=$this->uid;
		$symptoms_desc=I('post.symptoms_desc');
		$taboo_that=I('post.taboo_that');
		$other_disease=I('post.other_disease');
		$is_perfect = true;
		$patient_info = $this->patientmember->where("user_id=".$uid)->find();
		
		if($patient_info['is_first'] == 1){
			foreach($patient_info as $a){
			    if(empty($a)){
			        $is_perfect = false;
			        break;
			    }
			}
			if($is_perfect){
				$res = array(
					"user_id" => $uid,
					'is_first' => 2,
				);
				$this->patientmember->save($res);
				setpoints($uid,2,20,'分','148',0);
				$balance = $this->user->where('id='.$uid)->getField('score');
				financial_log($uid,20,3,$balance,'完善档案',8,2);
			}
		}
			
		
		if(empty($uid))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请先登录！";
			outJson($data);	
		}
		$res=array(
			'symptoms_desc' => $symptoms_desc,
			'taboo_that' => $taboo_that,
			'other_disease' => $other_disease,
		);
		$result=$this->patientmember->where("user_id=".$uid)->save($res);
		unset($data);
		$data['code']=1;
		$data['message']="编辑成功！";
		outJson($data);	
	}
	

}
?>