<?php
// +----------------------------------------------------------------------
// | 我的收藏接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class MycollectionController extends UserbaseController {
		function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->forum =D("forum");
		$this->forumcollection =D("ForumOperation");
		$this->patient =D("Patientmember");
		$this->member =D("Member");
		$this->user = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	public function form_collect(){
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$results = $this->forumcollection->field('forum_id,user_id,createtime')->where("user_id='".$this->uid."' and type=2")->limit($p.",".$limit)->select();
		foreach ($results as &$row) {
			unset($res);
			unset($rest);
			
			$rest=$this->forum->where("id=".$row['forum_id'])->find();
			$res=$this->member->where("user_id=".$rest['user_id'])->find();
			$row['name']=$res['name'];
			$row['head_img']=$res['img_thumb'];
			$row['createtime']=date("Y/m/d",$row['createtime']);
			$row['type']=$rest['type'];
			$row['title']=$rest['title'];
			$row['item_thumb']=explode(',',$rest['item_thumb']);
			$row['video']=$rest['video'];
			$row['content']=$rest['content'];
		}
		if($results)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info']=$results;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无信息";
			$data['info']=array();
			outJson($data);
		}
			
	}

	public function del_collect(){
		$uid = $this->uid; //患者id
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
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] ="收藏不存在！";
			outJson($data);
		}
		$result = $this->forum->field('title,item_thumb,content,video,type')->where("id='".$id."'")->find();
		if($result){
			$result['item_thumb']=explode(',',$result['item_thumb']);
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