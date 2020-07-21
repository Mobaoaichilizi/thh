<?php
// +----------------------------------------------------------------------
// | 收藏详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class MycollectionController extends DoctorbaseController {
		function _initialize() {
		$token=$_REQUEST['token'];
		accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->forum =D("forum");
		$this->forumcollection =D("ForumOperation");
	}
public function form_collect(){
		$id = I('post.id');//收藏id
		$results = $this->forumcollection->field('forum_id')->where("id='".$id."' and type = 2")->find();
		$result = $this->forum->where('id='.$forum_id)->find();
		
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info']=$result;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息加载失败！";
			outJson($data);
		}
			
	}


}
?>