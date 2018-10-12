<?php
// +----------------------------------------------------------------------
// | 关于广正接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class CommonController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		parent::_initialize();
		$this->user = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	public function share_wx(){
		$id = $this->uid;
		$share_number = $this->user->where("id=".$id)->getfield('share_number');
		$info['share_number'] = $this->host_url.'index.php?g=Home&m=download&a=index?share_number='.$share_number;
		$info['logo'] = 'oss/2018-04-10/15233432403773.png';
		unset($data);
		$data['code']=1;
		$data['message']="用户信息加载成功！";
		$data['info'] = $info;
		outJson($data);
	}
	
}
?>