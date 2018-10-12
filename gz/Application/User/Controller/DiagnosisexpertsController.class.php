<?php
// +----------------------------------------------------------------------
// | 寻医诊疗专家列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class DiagnosisexpertsController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->department =D("Department");
		$this->goods =D("Goods");
		
	}
	//寻医诊疗专家列表
	public function lists()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$settimg_id=!empty($_POST['settimg_id']) ? $_POST['settimg_id'] : 0;
		// $department_id=!empty($_POST['department_id']) ? $_POST['department_id'] : 0;
		$longitude=!empty($_POST['longitude']) ? $_POST['longitude'] : 0;
		$latitude=!empty($_POST['latitude']) ? $_POST['latitude'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		$default_id=!empty($_POST['default_id']) ? $_POST['default_id'] : 0;
		if(empty($settimg_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择疾病！";
			outJson($data);	
		}
		$p=($p-1)*$limit;
		
		// if($settimg_id!==0)
		// {
		// 	$sqlwhere.=" and lgq_member.department_id=".$settimg_id;
		// }
		if($typesort==1)
		{
			$orderwhere="juli asc,";
		}
		if($settimg_id!==0){
			$sqlwhere.=" and find_in_set(".$settimg_id.",lgq_member.settimg_id)";
		}
		$result=$this->member->join('lgq_user on lgq_member.user_id=lgq_user.id','left')->join('lgq_hospital on lgq_member.hospital_id=lgq_hospital.id','left')->field("lgq_member.user_id as id,lgq_member.name as name,lgq_member.img_thumb as img_thumb,lgq_member.professional as professional,lgq_member.profile as profile,lgq_user.member_level as level,lgq_user.is_login as is_login,ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((".$latitude." * PI() / 180 - lgq_hospital.lat * PI() / 180) / 2),2) + COS(".$latitude." * PI() / 180) * COS(lgq_hospital.lat * PI() / 180) * POW(SIN((".$longitude." * PI() / 180 - lgq_hospital.lng * PI() / 180) / 2),2))) * 1000) AS juli")->where("lgq_member.status=1 and lgq_user.role=1 ".$sqlwhere)->order($orderwhere."lgq_user.member_level desc,lgq_user.is_login asc,lgq_user.createtime desc")->limit($p.",".$limit)->select();
		if($result)
		{
			foreach($result as &$row)
			{
				$row['juli']=round($row['juli']/1000,2);	
			}
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
	public function recommend_goods()
	{
		$settimg_id=I('post.settimg_id');
		if(empty($settimg_id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择病症！";
			outJson($data);	
		}
		$result=$this->goods->field("id,title,price,img_thumb")->where("status=0 and category_id=".$settimg_id." and recommend=1")->order("createtime desc")->limit("0,4")->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="产品推荐获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="暂无商品";
			outJson($data);	
		}
	}
	
	

}
?>