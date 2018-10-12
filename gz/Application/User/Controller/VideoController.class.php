<?php
// +----------------------------------------------------------------------
// | 我的视频诊疗接口
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class VideoController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->video =D("Videodia");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
	public function video_list()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$ress=array();
		$result = $this->video->field("id,member_id,patientmember_id,status,create_time,type,money")->where("patientmember_id='".$uid."'")->order("id desc")->limit($p.",".$limit)->select();
		foreach ($result as $key => $value) {
			$res['id'] = $value['id'];
			$res['status'] = $value['status'];
			$res['type'] = $value['type'];
			$res['name'] = $this->doctor->where("user_id='".$value['member_id']."'")->getfield("name");
			$res['img_thumb'] = $this->doctor->where("user_id='".$value['member_id']."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$res['create_time'] = time_tran($value['create_time']);
			$ress[] = $res;
		}

		if($ress)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $ress;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="没有信息了！";
			$data['info']=array();
			outJson($data);	
		}
		
	}
}
?>