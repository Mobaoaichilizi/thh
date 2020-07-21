<?php
// +----------------------------------------------------------------------
// | 邀请好友接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class VersionController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->version = D('Version');
		$this->uid=D("User")->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
public	function do_version()
	{
		$id = $this->uid;

		
	}

}
?>