<?php
// +----------------------------------------------------------------------
// | 系统消息接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class SystemsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->systemsinfo = D('SystemsInfo');
		$this->information = D('Information');
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		$this->graphic = D('Graphic');
		$this->telephone = D('Telephone');
		$this->reserve = D('Reserve');
		
	}
//系统消息
public	function systems_info(){
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->systemsinfo->field("title,description,type_id,type,createtime")->where("(receive_uid = ".$this->uid." and type=1) or receive_uid = '0_0' or receive_uid = '0_1'")->limit($p.",".$limit)->order("createtime desc")->select();

		foreach ($result as &$val) {
			$val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
			if($val['type'] == "2"){
				$val['status'] = $this->graphic->where('id ='.$val['type_id'])->getfield('status');
			}else if($val['type'] == "3"){
				$val['status'] = $this->telephone->where('id ='.$val['type_id'])->getfield('status');
			}else if($val['type'] == "4"){
				$val['status'] = $this->reserve->where('id ='.$val['type_id'])->getfield('status');
			}else{
				$val['status'] = '';
			}

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
		if(empty($id))
		{
			unset($data);
			$data['code']=0; 
			$data['message']='消息不存在！';
			outJson($data);
		}
		$result = $this->information->field("id,title,content,description,update_time")->where("id =".$id)->find();
		$result['update_time'] = date('Y-m-d H:i:s',$result['update_time']);
		$result['content'] = html_entity_decode($result['content']);
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