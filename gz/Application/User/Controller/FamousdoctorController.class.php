<?php
// +----------------------------------------------------------------------
// | 名医工作室列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class FamousdoctorController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->department =D("Department");
		$this->goods =D("Goods");
		
	}
	//名医工作室列表
	public function lists()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$department_id=!empty($_POST['department_id']) ? $_POST['department_id'] : 0;
		$longitude=!empty($_POST['longitude']) ? $_POST['longitude'] : 0;
		$latitude=!empty($_POST['latitude']) ? $_POST['latitude'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		$default_id=!empty($_POST['default_id']) ? $_POST['default_id'] : 0;
		$p=($p-1)*$limit;
		
		if($department_id!==0)
		{
			$sqlwhere.=" and lgq_member.department_id=".$department_id;
		}
		if($typesort==1)
		{
			$orderwhere="juli asc,";
		}
		$result=$this->member->join('lgq_user on lgq_member.user_id=lgq_user.id','left')->join('lgq_hospital on lgq_member.hospital_id=lgq_hospital.id','left')->field("lgq_member.user_id as id,lgq_member.name as name,lgq_member.img_thumb as img_thumb,lgq_member.professional as professional,lgq_member.profile as profile,lgq_user.member_level as level,lgq_user.is_login as is_login,ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((".$latitude." * PI() / 180 - lgq_hospital.lat * PI() / 180) / 2),2) + COS(".$latitude." * PI() / 180) * COS(lgq_hospital.lat * PI() / 180) * POW(SIN((".$longitude." * PI() / 180 - lgq_hospital.lng * PI() / 180) / 2),2))) * 1000) AS juli")->where("lgq_member.status=1 and lgq_user.role=1 and lgq_member.is_position=1 ".$sqlwhere)->order($orderwhere."lgq_user.member_level desc,lgq_user.is_login asc,lgq_user.createtime desc")->limit($p.",".$limit)->select();
		// dump($this->member->getlastsql());
		foreach ($result as $key => &$value) {
			$value['doctor_id'] = md5(md5($value['id']));
			$value['juli']=round($value['juli']/1000,2);
		}
		
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="获取医生列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无医生！";
			$data['info']=array();
			outJson($data);	
		}
	}
	//科室分类
	public function departmentlist()
	{
		$result=$this->department->field("id,name,img_thumb")->where("status=1 and type=1")->order('sort asc')->select();
		
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="科室分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="暂无分类！";
			outJson($data);	
		}
		
	}
	

}
?>