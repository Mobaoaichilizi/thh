<?php
// +----------------------------------------------------------------------
// | 我的接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class MineController extends DoctorbaseController {
	function _initialize() {
		parent::_initialize();
		
	}
	
	
public	function do_phone()
	{
		$info['title'] = C('WEB_TITLE');
		$info['phone'] = C('WEB_PHONE');
		

		if($info)
		{
			unset($data);
			$data['code']=1;
			$data['message']="加载成功！";
			$data['info']=$info;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="加载失败！";
			outJson($data);
		}
		
	}
	
}
?>