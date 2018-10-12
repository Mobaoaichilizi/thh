<?php
// +----------------------------------------------------------------------
// | 美容瘦身机构详情管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class BeautydealController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->institutions = D("Institutions");
		
	}
	//美容瘦身机构详情
	public function deal()
	{	
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="机构不存在";
			outJson($data);	
		}
		$result=$this->institutions->field("id,name,phone,level,description,img_item,address")->where("status=1 and id=".$id)->find();
		if($result)
		{
			$result['img_item']=explode(',',$result['img_item']);
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息不存在";
			outJson($data);	
		}
	}
	
	public function service(){
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="机构不存在";
			outJson($data);	
		}
		$result=$this->catalog->field("id,title,img_thumb")->where("institutions_id=".$id)->limit("0,6")->select();
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
			$data['code']=1;
			$data['message']="信息不存在";
			$data['info']=array();
			outJson($data);	
		}
	}
	public function service_deal(){
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="服务目录不存在";
			outJson($data);	
		}
		$result=$this->catalog->field("id,title,img_thumb,img_item,description,content")->where("id=".$id)->find();
		if($result)
		{
			$result['img_item']=explode(',',$result['img_item']);
			unset($data);
			$data['code']=1;
			$data['message']="获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息不存在";
			outJson($data);	
		}
	}

}
?>