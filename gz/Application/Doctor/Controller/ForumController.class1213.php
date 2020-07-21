<?php
// +----------------------------------------------------------------------
// | 论坛管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class ForumController extends DoctorbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->setting =D("Setting");
		$this->forum =D("Forum");
		$this->member =D("Member");
		$this->forumoperation =D("ForumOperation");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield("id");
		
	}
	//论坛分类展示
	public function forum_cate()
	{
		$result=$this->setting->field("id,title")->where("parentid=21")->order("sort asc")->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
				$val['lower']=$this->setting->field("id,title")->where("parentid=".$val['id'])->order("sort asc")->select();
			}
			unset($data);
			$data['code']=1;
			$data['message']="分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="分类获取成功！";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	//论坛信息展示
	public function forum_list()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$setting_id=!empty($_POST['setting_id']) ? $_POST['setting_id'] : 0;
		$p=($p-1)*$limit;
		if($setting_id!==0)
		{
			$sqlwhere.=" and setting_id=".$setting_id;
		}
		
		$result=$this->forum->field("id,title,user_id,item_thumb,video,audio,content,type,create_time")->where("status=1 ".$sqlwhere)->order("create_time desc")->limit($p.",".$limit)->select();
		if($result)
		{
			foreach($result as &$val)
			{
				unset($user_info);
				$user_info=$this->member->field("name,img_thumb")->where("user_id=".$val['user_id'])->find();
				unset($count_praise);
				$count_praise=$this->forumoperation->where("forum_id=".$val['id']." and user_id=".$this->uid." and type=1")->count();
				unset($count_collection);
				$count_collection=$this->forumoperation->where("forum_id=".$val['id']." and user_id=".$this->uid." and type=2")->count();
				$val['head_img']=$user_info['img_thumb'];
				$val['item_thumb']=explode(',',$val['item_thumb']);
				// $val['video']=explode(',',$val['video']);
				// $val['audio']=explode(',',$val['audio']);
				$val['user_name']=$user_info['name'];
				$val['create_time']=time_tran($val['create_time']);
				 if($count_praise > 0)
				 {
				 	$val['is_praise']=1;
				 }else
				 {
				 	$val['is_praise']=0;
				 }
				 if($count_collection > 0)
				 {
				 	$val['is_collection']=1;
				 }else
				 {
			 	$val['is_collection']=0;
				 }
				$val['praise_count']=$this->forumoperation->where("forum_id=".$val['id']." and type=1")->count();
				$val['collection_count']=$this->forumoperation->where("forum_id=".$val['id']." and type=2")->count();
			    $val['share_count']=$this->forumoperation->where("forum_id=".$val['id']." and type=3")->count();
			}
			unset($data);
			$data['code']=1;
			$data['message']="分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无分类！";
			$data['info']=array();
			outJson($data);	
		}
	}
//发表	
public function published()
	{
		$uid = $this->uid;
		$type = I('post.type');//论坛类型 1：图文 2：视频
		$title = I('post.title');
		$content = I('post.content');
		$setting_id = I('post.setting_id');
		$item_thumb = I('post.item_thumb');
		$video = I('post.video');
		$audio = I('post.audio');
		if(empty($title)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请输入标题！";
			outJson($data);
		}
		if(empty($type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择发表的类型！";
			outJson($data);
		}
		if(empty($setting_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择发布类别";
			outJson($data);
		}
		$res = array(
			'user_id' => $this->uid,
			'setting_id' => $setting_id,
			'title'    => $title,
			'item_thumb'    => $item_thumb,
			'video' => $video,
			'audio' => $audio,
			'content' => $content,
			'create_time' => time(),
			'type' => $type,
		);
		$jt = strtotime(date("Y-m-d",time()).' 00:00:00');
		$is = $this->forum->where('user_id='.$uid)->order('create_time desc')->getField("create_time"); 
		if(!empty($is)){
			if($is>$jt){
				$is_first = 1;
			}else{
				$is_first = 0;
				if($type == 1){
					setpoints($uid,1,0.2,'元','145',0);
					$balance = $this->user->where('id='.$uid)->getField('balance');
					financial_log($uid,0.2,3,$balance,'当天首次发表奖励',8);
				}else if($type == 2){
					setpoints($uid,1,0.5,'元','145',0);
					$balance = $this->user->where('id='.$uid)->getField('balance');
					financial_log($uid,0.5,3,$balance,'当天首次发表奖励',8);
				}
			}
		}else{
			$is_first = 0;
			if($type == 1){
					setpoints($uid,1,0.2,'元','145',0);
					$balance = $this->user->where('id='.$uid)->getField('balance');
					financial_log($uid,0.2,3,$balance,'当天首次发表奖励',8);
				}else if($type == 2){
					setpoints($uid,1,0.5,'元','145',0);
					$balance = $this->user->where('id='.$uid)->getField('balance');
					financial_log($uid,0.5,3,$balance,'当天首次发表奖励',8);
				}
		}
		$result = $this->forum->add($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "发布成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "发布失败！";
			outJson($data);
		}

	}
//论坛详情
public function forum_details()
	{
		$id = I('post.id');//论坛ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择访问的论坛！";
			outJson($data);
		}
		$result = $this->forum->where("id=".$id)->find();
		if($result){
			$result['item_thumb']=explode(',',$result['item_thumb']);
			unset($data);
			$data['code'] = 1;
			$data['message'] = "内容加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "您访问的内容不存在！";
			outJson($data);
		}

	}
	//点赞收藏接口
	public function do_praise()
	{
		$id = I('post.id');//论坛ID
		$type = I('post.type');//点赞类型 1：点赞 2：收藏
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "此信息不存在！";
			outJson($data);
		}
		if(empty($type)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "类型不能为空！";
			outJson($data);
		}
		$res_count=$this->forum->where("id=".$id." and user_id=".$this->uid)->count();
		if($res_count > 0)
		{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "您不能给自己评论！";
			outJson($data);
		}
		if($type==1)
		{
			$count=$this->forumoperation->where("forum_id=".$id." and user_id=".$this->uid." and type=1")->count();
			if($count > 0)
			{
				unset($data);
				$data['code'] = 0;
				$data['message'] = "您已经点过赞了";
				outJson($data);
			}
		}else if($type==2)
		{
			$count=$this->forumoperation->where("forum_id=".$id." and user_id=".$this->uid." and type=2")->count();
			if($count > 0)
			{
				unset($data);
				$data['code'] = 0;
				$data['message'] = "您已经收藏过了";
				outJson($data);
			}
		}
		
		$res = array(
			'user_id' => $this->uid,
			'forum_id' => $id,
			'type'    => $type,
			'create_time' => time(),
		);
		$result = $this->forumoperation->add($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "失败！";
			outJson($data);
		}
	}

}
?>