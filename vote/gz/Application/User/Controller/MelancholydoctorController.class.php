<?php
// +----------------------------------------------------------------------
// | 寻医诊疗医生列表页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MelancholydoctorController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->department =D("Department");
		$this->member =D("Member");
		
	}
	//首页banner
	public function doctorlist()
	{
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$department_id=!empty($_POST['department_id']) ? $_POST['department_id'] : 0;
		$p=($p-1)*$limit;
		if($department_id!==0)
		{
			$sqlwhere.=" and department_id=".$department_id;
		}
		$result=$this->member->field("user_id,name,img_thumb,department_id,profile")->where("1=1 ".$sqlwhere)->order("id asc")->limit($p.",".$limit)->select();
		if($result)
		{
			foreach($result as $row)
			{
				unset($star_number);
				$row['img_thumb']=$this->host.$row['img_thumb'];
				$res[]=$row;	
			}
			unset($data);
			$data['code']=1;
			$data['message']="获取列表成功";
			$data['info']=$res;
			outJson($data);
		}else
		{
			$data['code']=1;
			$data['message']="已经到最底部了";
			$data['info']=array();
			outJson($data);
		}
		
	}

}
?>