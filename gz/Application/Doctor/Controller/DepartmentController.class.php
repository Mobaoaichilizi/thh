<?php
// +----------------------------------------------------------------------
// | 科室接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class DepartmentController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->department =D("Department");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
	public function department_list()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		// $department = I('post.department');
		// if(!empty($department)){
		// 	$res = array(
		// 		'name' => $department,
		// 		'status' => 1,
		// 		'create_time' => time(),
		// 	);
		// 	$result = $this->department->add($res);
		// 	if($result){
		// 		unset($data);
		// 		$data['code'] = 1;
		// 		$data['message'] = "手动填写科室成功！";
		// 		$data['department_id'] = $result;
		// 		outJson($data);
		// 	}
		// }
		$result=$this->department->field("id,name")->order('sort asc,create_time desc')->limit($p.",".$limit)->where('status=1')->select();
		
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