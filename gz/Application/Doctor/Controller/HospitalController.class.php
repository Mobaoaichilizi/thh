<?php
// +----------------------------------------------------------------------
// | 医院接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class HospitalController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->hospital =D("Hospital");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
	public function hospital_list()
	{
		$uid = $this->uid;
		// $hospital = I('post.hospital');
		// if(!empty($hospital)){
		// 	$res = array(
		// 		'name' => $hospital,
		// 		'status' => 1,
		// 		'create_time' => time(),
		// 		'update_time' => time(),
		// 	);
		// 	$result = $this->hospital->add($res);
		// 	if($result){
		// 		unset($data);
		// 		$data['code'] = 1;
		// 		$data['message'] = "手动填写医院成功！";
		// 		$data['hospital_id'] = $result;
		// 		outJson($data);
		// 	}
		// }
		$result=$this->hospital->field("id,name")->order('create_time desc')->where('status=1')->select();
		
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['info']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="数据错误！";
			outJson($data);

		}
		
	}
	

}
?>