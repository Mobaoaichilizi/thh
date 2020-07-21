<?php
// +----------------------------------------------------------------------
// | 系统消息接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class SystemsController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->systemsinfo = D('SystemsInfo');
		$this->information = D('Information');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
//系统消息
public	function systems_info(){
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->systemsinfo->field("title,description,type_id,type,createtime")->where("(receive_uid = ".$this->uid." and type=1) or receive_uid = '0_0' or receive_uid = '0_2'")->order('createtime desc')->limit($p.",".$limit)->select();
		foreach ($result as &$val) {
			$val['createtime'] = time_tran($val['createtime']);
		}
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "消息加载成功！";
			$data['info'] = $result;
			outjson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无消息！";
			$data['info'] = array();
			outjson($data);
		}

	}

	//系统消息详情
	public	function systems_details(){
		$uid = $this->uid;
		$id = I('post.id');
		// $id = 15;
		if(empty($id))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='消息不存在！';
			outJson($data);
		}
		$result = $this->information->field("id,title,content,description,update_time")->where("id =".$id)->find();
		$result['content'] = html_entity_decode($result['content']);
		$result['update_time'] = date('Y-m-d H:i:s',$result['update_time']);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "消息加载成功！";
			$data['info'] = $result;
			outjson($data);
		}else{
			unset($data);
			$data['code'] = 2;
			$data['message'] = "暂无消息！";
			outjson($data);
		}

	}


}
?>