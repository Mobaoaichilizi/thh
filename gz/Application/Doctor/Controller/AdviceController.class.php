<?php
// +----------------------------------------------------------------------
// | 投诉建议接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class AdviceController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->advice   = D('Advice');
		$this->user   = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
	
public	function do_advice()
	{
		$uid = $this->uid;
		$img_thumb = I('post.img_thumb');
		$content = I('post.content');
		if(empty($content))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入您的意见！";
			outJson($data);
		}
		$res=array(
			'user_id' => $uid,
			'img_thumb' => $img_thumb,
			'content' => $content,
			'create_time' => time(),
		);
		$result=$this->advice->add($res);

		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="提交成功！";
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="提交失败！";
			outJson($data);
		}
		
	}
	
}
?>