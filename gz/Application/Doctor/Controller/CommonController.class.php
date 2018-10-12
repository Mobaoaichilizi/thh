<?php
// +----------------------------------------------------------------------
// | 提现接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class CommonController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		parent::_initialize();
		$this->user = D('User');
		$this->member = D('Member');
		$this->patient = D('Patientmember');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	

public function user_info()
	{
		$id = $this->uid;
		$doctor = $this->member->where("user_id=".$id)->count();
		$patient = $this->patient->where("user_id=".$id)->count();
		if($doctor > 0){
			$info = $this->member->field("name,img_thumb")->where("user_id=".$id)->find();
		}else if($patient > 0){
			$info = $this->patient->field("name,img_thumb")->where("user_id=".$id)->find();
		}else{
			unset($data);
			$data['code']=0;
			$data['message']="此用户不存在！";
			outJson($data);
		}

		unset($data);
		$data['code']=1;
		$data['message']="用户信息加载成功！";
		$data['info'] = $info;
		outJson($data);
			
		

	}
	public function share_wx(){
		$id = $this->uid;
		$share_number = $this->user->where("id=".$id)->getfield('share_number');
		$info['share_number'] = $this->host_url.'index.php?g=Home&m=download&a=index?share_number='.$share_number;
		$info['logo'] = 'oss/2018-04-10/15233432403773.png';
		unset($data);
		$data['code']=1;
		$data['message']="用户信息加载成功！";
		$data['info'] = $info;
		outJson($data);
	}

}
?>