<?php
// +----------------------------------------------------------------------
// | 我的收藏接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class MycollectionController extends DoctorbaseController {
		function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->forum =D("forum");
		$this->forumcollection =D("ForumOperation");
		$this->member =D("Member");
		$this->user = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function form_collect(){
		$id = $this->uid;//医生Id
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$results = $this->forumcollection->field('forum_id')->where("user_id='".$id."' and type = 2")->limit($p.",".$limit)->select();
		foreach ($results as $k => $value) {
			$result[$k] = $this->forum->join("lgq_user on lgq_user.id=lgq_forum.user_id")->field("lgq_forum.id,lgq_forum.title,lgq_forum.item_thumb,lgq_forum.video,lgq_forum.audio,lgq_forum.content,lgq_forum.type,lgq_forum.create_time,lgq_user.id as uid")->where("lgq_forum.id='".$value['forum_id']."' and status=1")->find();
			$result[$k]['name'] = $this->member->where("user_id='".$result['uid']."'")->getField('name');
			$result[$k]['img_thumb'] = $this->member->where("user_id='".$result['uid']."'")->getField('img_thumb');
			$result[$k]['create_time'] = date("Y-m-d",$result[$k]['create_time']);
		}

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
			$data['code']=2;
			$data['message']="暂无收藏！";
			$data['info']=array();
			outJson($data);
		}
			
	}

public function del_collect(){
		$uid = $this->uid; //医生id
		$ids = I('post.ids');//要删除的id
		if(empty($ids)){
			unset($data);
			$data['code'] = 0;
			$data['message'] ="请选择要删除的收藏！";
			outJson($data);
		}
		$result = $this->forumcollection->where("forum_id in(".$ids.") and type = 2 and user_id = '".$uid."'")->delete();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "删除成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "删除失败！";
			outJson($data);
		}
	}
public function collect_details(){
		$uid = $this->uid;
		$id = I('post.id');
		$result = $this->forum->field('title,item_thumb,content,video,audio,create_time')->where("id='".$id."'")->find();
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "详情加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}

	}
}
?>