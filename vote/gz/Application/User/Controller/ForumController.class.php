<?php
// +----------------------------------------------------------------------
// | 论坛管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class ForumController extends UserbaseController {
	public function _initialize() {
		
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->setting =D("Setting");
		$this->forum =D("Forum");
		$this->member =D("Member");
		$this->patientmember =D("Patientmember");
		$this->forumoperation =D("ForumOperation");
		$this->user = D('User');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
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
			$data['code']=2;
			$data['message']="暂无分类！";
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
		$uid = $this->uid;
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
		// $res_count=$this->forum->where("id=".$id." and user_id=".$this->uid)->count();
		// if($res_count > 0)
		// {
		// 	unset($data);
		// 	$data['code'] = 0;
		// 	$data['message'] = "您不能给自己评论！";
		// 	outJson($data);
		// }
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
		$starttime = time()-86400;
		$res_count=$this->forumoperation->where("user_id='".$uid."' and type=3 and createtime > ".$starttime)->count();
		if($res_count > 10)
		{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "每天最多分享十次！";
			outJson($data);
		}
		$res = array(
			'user_id' => $this->uid,
			'forum_id' => $id,
			'type'    => $type,
			'createtime' => time(),
		);
		$result = $this->forumoperation->add($res);
		if($type == 3){
			$level = $this->user->where('id='.$uid)->getField("member_level");
			if($level == 0){
				setpoints($uid,2,1,'分','146',0);
				$score = $this->user->where("id=".$uid)->getField('score');
				financial_log($uid,1,3,$score,'分享文章奖励',8,2);
			}else if($level == 1){
				setpoints($uid,2,2,'分','146',0);
				$score = $this->user->where("id=".$uid)->getField('score');
				financial_log($uid,2,3,$score,'分享文章奖励',8,2);
			}else if($level == 2){
				setpoints($uid,2,3,'分','146',0);
				$score = $this->user->where("id=".$uid)->getField('score');
				financial_log($uid,3,3,$score,'分享文章奖励',8,2);
			}
			
		}
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