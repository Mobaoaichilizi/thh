<?php
// +----------------------------------------------------------------------
// | 网联医院详情管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class SnatchedhospitaldealController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->institutions = D("Institutions");
		
	}
	//网联医院详情
	public function deal()
	{	
		$id=I('post.id');
		// $id='25';
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="机构不存在";
			outJson($data);	
		}
		$result=$this->institutions->field("id,name,phone,description,img_item,address")->where("status=1 and id=".$id)->find();
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