<?php
// +----------------------------------------------------------------------
// | 投诉建议管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class AdviceController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->advice =D("Advice");
	}
	//添加投诉建议
	public function advice_add()
	{

		$uid=I('post.uid');
		$img_thumb=I('post.img_thumb');
		$content=I('post.content');
		if(empty($content))
		{
			unset($data);
			$data['code']=0;
			$data['message']="内容不能为空！";
			outJson($data);	
		}
		if($uid!=0)
		{
			$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
			$res=array(
				'user_id' => $this->uid,
				'content' => $content,
				'img_thumb' => $img_thumb,
				'create_time' => time(),
			);			
		}else
		{
			$res=array(
				'content' => $content,
				'img_thumb' => $img_thumb,
				'create_time' => time(),
			);
		}
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