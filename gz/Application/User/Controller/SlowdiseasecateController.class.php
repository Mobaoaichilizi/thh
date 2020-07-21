<?php
// +----------------------------------------------------------------------
// | 慢病调理分类文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class SlowdiseasecateController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->setting =D("Setting");
		$this->department =D("Department");
		$this->adv =D("Adv");
	}
	public function banner()
	{
		$result=$this->adv->field("title,img_thumb")->where("adv_id=91")->order('sort asc')->select();
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
	//疾病分类
	public function lists()
	{
		$result=$this->setting->field("id,title,img_thumb")->where("status=1 and parentid=88")->order('sort asc')->select();
		// $result=$this->department->field("id,name as title,img_thumb")->where("status=1 and type in (3,4)")->order('sort asc')->select();
		
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="疾病分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=2;
			$data['message']="暂无分类！";
			outJson($data);	
		}
		
	}

}
?>