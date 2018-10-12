<?php
// +----------------------------------------------------------------------
// | 我要体检首页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MedicalindexController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->setting =D("Setting");
		$this->adv =D("Adv");
		$this->institutions = D("Institutions");
		$this->information = D("Information");
		$this->goods =D("Goods");
	}
	//banner
	public function banner()
	{
		$result=$this->adv->field("title,img_thumb")->where("adv_id=95")->order('sort asc')->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="banner图获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=2;
			$data['message']="暂无图片！";
			outJson($data);	
		}
	}
	//资讯
	public function news()
	{
		$result=$this->information->field("id,title")->where("setting_id=108")->order('update_time desc')->select();
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="资讯获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=2;
			$data['message']="暂无资讯！";
			outJson($data);	
		}
	}
	//机构信息
	public function lists()
	{
		$result=$this->institutions->field("id,name,img_thumb")->where("status=1 and settimg_id=101")->order('create_time desc')->limit('0,6')->select();
		
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="机构获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=2;
			$data['message']="暂无机构！";
			outJson($data);	
		}
		
	}
	//推荐产品
	public function recommend_goods()
	{
		$result=$this->goods->field("id,title,price,img_thumb")->where("status=0 and category_id=104 and recommend=1")->order("createtime desc")->limit("0,4")->select();
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
			$data['code']=2;
			$data['message']="暂无商品";
			outJson($data);	
		}
	}

}
?>