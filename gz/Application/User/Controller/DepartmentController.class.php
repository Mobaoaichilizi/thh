<?php
// +----------------------------------------------------------------------
// | 主页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class DepartmentController extends UserbaseController {
	public function _initialize() {
		$token=$_REQUEST['token'];
		accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->department =D("Department");
		
	}
	//首页banner
	public function departmentlist()
	{
		$result=$this->department->field("id,name,img_thumb")->where("status=1")->order('sort asc')->select();
		
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