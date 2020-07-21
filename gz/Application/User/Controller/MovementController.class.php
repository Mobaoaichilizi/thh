<?php
// +----------------------------------------------------------------------
// | 健康运动机构列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MovementController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->institutions = D("Institutions");
		
	}
	//机构列表
	public function lists()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$longitude=!empty($_POST['longitude']) ? $_POST['longitude'] : 0;
		$latitude=!empty($_POST['latitude']) ? $_POST['latitude'] : 0;
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		
		$p=($p-1)*$limit;
		
		if($typesort==1)
		{
			$orderwhere="sort desc,";
		}else if($typesort==2)
		{
			$orderwhere="juli asc,";
		}
		$result=$this->institutions->field("id,name,img_thumb,level,description,ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((".$latitude." * PI() / 180 - lat * PI() / 180) / 2),2) + COS(".$latitude." * PI() / 180) * COS(lat * PI() / 180) * POW(SIN((".$longitude." * PI() / 180 - lng * PI() / 180) / 2),2))) * 1000) AS juli")->where("settimg_id=98 ")->order($orderwhere."level desc,create_time desc")->limit($p.",".$limit)->select();
		if($result)
		{
			foreach($result as &$row)
			{
				$row['juli']=round($row['juli']/1000,2);	
			}
			unset($data);
			$data['code']=1;
			$data['message']="获取列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无机构！";
			$data['info']=array();
			outJson($data);	
		}
	}
	

}
?>