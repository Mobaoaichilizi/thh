<?php
// +----------------------------------------------------------------------
// | 在线会诊管理接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class OnlineController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		require_once VENDOR_PATH.'rongyun/ServerAPI.php';
		$this->rongyun = new \ServerAPI('3argexb630vte','QhioQ2KX4Hmf');
		$this->member = D('Member');
		$this->user = D('User');
		$this->onlinetreat =D("Onlinetreat");
		$this->onlinetreatuser =D("Onlinetreatuser");
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//创建群组
	public function create_group()
	{
		$group_name=I('post.group_name');
		$img_thumb=I('post.img_thumb');
		$description=I('post.description');
		if(empty($group_name))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id对应的名称不能为空！";
			outJson($data);	
		}
		$res=array(
			'user_id' => $this->uid,
			'title' => $group_name,
			'img_thumb' => $img_thumb,
			'description' => $description,
			'create_time' => time(),
		);
		$result=$this->onlinetreat->add($res);
		if($result)
		{
			$res=array(
				'online_id' => $result,
				'join_user' => md5(md5($this->uid)),
				'user_id' => $this->uid,
				'join_user_id' => $this->uid,
				'is_group' => 1,
				'createtime' => time(),
			);
			$this->onlinetreatuser->add($res);
			$this->rongyun->groupCreate($this->uid,$result,$group_name);
			//$this->rongyun->groupJoin($this->uid,$result,$group_name);
			unset($res);
			$res['uid']=$this->uid;
			$res['id']=$result;
			unset($data);
			$data['code']=1;
			$data['message']="创建的群组成功！";
			$data['info']=$res;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="创建的群组失败！！";
			outJson($data);	
		}
	}
	//群组列表
	public function group_list()
	{
		$type=I('post.type');
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		if(empty($type))
		{
			unset($data);
			$data['code']=0;
			$data['message']="类型不能为空！";
			outJson($data);	
		}
		if($type==1)
		{
			$result=$this->onlinetreat->field("id,user_id,description,title,create_time,status")->where("user_id=".$this->uid." and status=1")->order("create_time desc")->limit($p.','.$limit)->select();
			foreach($result as &$row)
			{
				$row['create_time']=time_tran($row['create_time']);
				$row['img_thumb'] = $this->member->where("user_id=".$row['user_id'])->getfield('img_thumb');
			}
		}else if($type==2)
		{
			$result=$this->onlinetreat->field("id,user_id,description,title,create_time,status")->where("id in (select online_id from lgq_onlinetreatuser where join_user='".md5(md5($this->uid))."' and is_group=0) and status=1")->order("create_time desc")->limit($p.','.$limit)->select();
			foreach($result as &$row)
			{
				$row['create_time']=time_tran($row['create_time']);
				$row['img_thumb'] = $this->member->where("user_id=".$row['user_id'])->getfield('img_thumb');
			}
		}
		if($result)
		{
			unset($data);
			$data['code']=1;
			$data['message']="群组列表";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无加入的群组";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	//删除群组
	public function del_group()
	{
		$id=I('post.id');

		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		$result=$this->onlinetreat->where("id=".$id." and user_id='".$this->uid."'")->delete();
		if($result)
		{
			$this->onlinetreatuser->where("online_id=".$id." and user_id='".$this->uid."'")->delete();
			$this->rongyun->groupDismiss($this->uid,$id);	
			unset($data);
			$data['code']=1;
			$data['message']="删除的群组成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="删除的群组失败！！";
			outJson($data);	
		}
		
	}
	//加入群组
	public function into_group()
	{
		$id=I('post.id');
		$join_user=I('post.join_user');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		if(empty($join_user))
		{
			unset($data);
			$data['code']=0;
			$data['message']="邀请人不存在！";
			outJson($data);	
		}
		$count=$this->onlinetreatuser->where("online_id=".$id." and join_user='".$join_user."'")->count();
		if($count > 0)
		{
			unset($data);
			$data['code']=0;
			$data['message']="您已经加入该群组！";
			outJson($data);	
		}
		$res=array(
			'online_id' => $id,
			'join_user' => $join_user,
			'user_id' => $this->uid,
			'createtime' => time(),
		);
		$result=$this->onlinetreatuser->add($res);
		$res=$this->onlinetreat->where("id=".$id)->find();
		if($result)
		{
			$this->rongyun->groupJoin($join_user,$id,$res['title']);
			unset($data);
			$data['code']=1;
			$data['message']="邀请成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="邀请失败！";
			outJson($data);	
		}
	}
	//退出群组
	public function out_group()
	{
	
		$id=I('post.id');
		$join_user=I('post.join_user');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		if(empty($join_user))
		{
			unset($data);
			$data['code']=0;
			$data['message']="邀请人不存在！";
			outJson($data);	
		}
		$result=$this->onlinetreatuser->where("online_id=".$id." and join_user='".$join_user."'")->delete();
		if($result)
		{
			$this->rongyun->groupQuit($join_user,$id);
			unset($data);
			$data['code']=1;
			$data['message']="退出成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="退出失败！！";
			outJson($data);	
		}	
	}
	//踢出成员
	public function outo_user()
	{
		$id=I('post.id');
		$join_user=I('post.join_user');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		if(empty($join_user))
		{
			unset($data);
			$data['code']=0;
			$data['message']="邀请人不存在！";
			outJson($data);	
		}
		$result=$this->onlinetreatuser->where("online_id=".$id." and join_user='".$join_user."'")->delete();
		if($result)
		{
			$this->rongyun->groupQuit($join_user,$id);
			unset($data);
			$data['code']=1;
			$data['message']="移除成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="移除失败！";
			outJson($data);	
		}
	}
	//邀请成员推送
	public function into_user()
	{
		$id=I('post.id');
		$join_user=I('post.join_user');
		$user_id=I('post.user_id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		if(empty($join_user))
		{
			unset($data);
			$data['code']=0;
			$data['message']="邀请人不存在！";
			outJson($data);	
		}
		$count=$this->onlinetreatuser->where("online_id=".$id." and join_user='".$join_user."'")->count();
		if($count > 0)
		{
			unset($data);
			$data['code']=0;
			$data['message']="您已经加入该群组！";
			outJson($data);	
		}
		$doctor_phone=$this->user->where("md5(md5(id))='".$join_user."' and role=1")->find();
		if($doctor_phone['deviceid']!='')
		{
			$title="";
			$content = "亲,您有会诊邀请！";
			$device[] = $doctor_phone['deviceid'];
			$extra = array("type" => "8", "type_id" => $id,'user_type' => 1,'status' => 1);
			$audience='{"alias":'.json_encode($device).'}';
			$extras=json_encode($extra);
			$os=$doctor_phone['os'];
			jpush_send($title,$content,$audience,$os,$extras);
			
			$res=array(
				'online_id' => $id,
				'join_user' => $join_user,
				'user_id' => $this->uid,
				'join_user_id' => $user_id,
				'createtime' => time(),
			);
			$this->onlinetreatuser->add($res);
			$rest=$this->onlinetreat->where("id=".$id)->find();
			$this->rongyun->groupJoin($join_user,$id,$rest['title']);
			unset($data);
			$data['code']=1;
			$data['message']="邀请成功！";
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="该医生不在线！";
			outJson($data);	
		}
		
	}
	//群里成员
	public function group_user_list()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		$result=$this->onlinetreatuser->where("online_id=".$id)->order("createtime desc")->select();
		if($result)
		{
			foreach($result as $row)
			{
				$res['user_id']=$row['join_user'];
				$res['join_user_id']=$row['join_user_id'];
				$res['is_group']=$row['is_group'];
				$res['name']=$this->member->where("md5(md5(user_id))='".$row['join_user']."'")->getField("name");
				$res['head_img']=$this->member->where("md5(md5(user_id))='".$row['join_user']."'")->getField("img_thumb");
				$rest[]=$res;
			}
			unset($data);
			$data['code']=1;
			$data['message']="人员列表";
			$data['info']=$rest;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无人员";
			$data['info']=array();
			outJson($data);	
		}
	}
	//结束会诊
	public function over_online()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		$result=$this->onlinetreat->where("id=".$id)->save(array('status' => 2));
		$doctor_user=$this->onlinetreat->where("id=".$id)->getField("user_id");
		$doctor_lists=$this->onlinetreatuser->where("online_id=".$id." and is_group=0")->select();
		foreach ($doctor_lists as $key => $value) {
			if($value['join_user_id']!=$doctor_user){
				$doctor_phone = $this->user->where("id=".$value['join_user_id'].' and role=1')->find();
				if($doctor_phone['deviceid']!='')
				{
				$title="";
				$content = "亲,您有会诊结束！";
				$device[] = $doctor_phone['deviceid'];
				$extra = array("type" => "8", "type_id" => $id,'user_type' => 1,'status' => 2);
				$audience='{"alias":'.json_encode($device).'}';
				$extras=json_encode($extra);
				$os=$doctor_phone['os'];
				jpush_send($title,$content,$audience,$os,$extras);
				}
			}
		}
		
		unset($data);
		$data['code']=1;
		$data['message']="结束会诊成功";
		outJson($data);	
		
	}
	//群组信息
	public function group_info()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="群组Id不能为空！";
			outJson($data);	
		}
		$result=$this->onlinetreat->field("id,user_id,img_thumb,description,title")->where("id=".$id)->find();
		$resc=$this->onlinetreatuser->where("online_id=".$id." and is_group=0")->order("createtime desc")->select();
	
		$rest=array();
		foreach($resc as $row)
		{
			$res['user_id']=$row['join_user'];
			$res['join_user_id']=$row['join_user_id'];
			$res['name']=$this->member->where("md5(md5(user_id))='".$row['join_user']."'")->getField("name");
			$res['head_img']=$this->member->where("md5(md5(user_id))='".$row['join_user']."'")->getField("img_thumb");
			$rest[]=$res;
		}
		unset($data);
		$data['code']=1;
		$data['message']="人员列表";
		$data['info']=$rest;
		$data['info_desc']=$result;
		outJson($data);	

		
	}
}
?>