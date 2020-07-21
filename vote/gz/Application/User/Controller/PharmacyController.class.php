<?php
// +----------------------------------------------------------------------
// | 药房管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class PharmacyController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->pharmacy =D("Pharmacy");
		$this->setting =D("Setting");
		$this->adv =D("Adv");
	}
	//中药炮制
	public function pharmacy_index()
	{

		$list=$this->setting->field("id,title,img_thumb")->where("parentid=66 and status=1")->order("sort asc")->select();
		$banner = $this->adv->field('img_thumb,title')->where('adv_id=35')->order('sort asc')->select();
		if($list)
		{
			$row['img_thumb']=$this->host.$list['img_thumb'];
			unset($data);
			$data['code']=1;
			$data['message']="获取信息成功！";
			$data['info']=$list;
			$data['banner']=$banner;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="暂无信息";
			outJson($data);	
		}
		
	}
	//成品中药
	public function pharmacy_medicine()
	{

		$list=$this->setting->field("id,title,img_thumb")->where("parentid=127 and status=1")->order("sort asc")->select();
		if($list)
		{
			$row['img_thumb']=$this->host.$list['img_thumb'];
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
	

}
?>