<?php
// +----------------------------------------------------------------------
// | 我的图文咨询接口
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class GraphicController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);//单点登录
		parent::_initialize();
		$this->user =D("User");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->graphic =D("Graphic");
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	//我的图文咨询列表
	public function graphic_list()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		$p=($p-1)*$limit;
		$ress=array();
		$result = $this->graphic->field("id,member_id,patientmember_id,status,create_time,type,money")->where("patientmember_id=".$uid)->order("id desc")->limit($p.",".$limit)->select();

		$list = array();
		foreach ($result as $k=>$vo) {
			$id=intval($vo['member_id']);
			 	$list[$id]=isset($list[$id])?
			 			(strtotime($vo['create_time'])>strtotime($list[$id]['create_time']))? $vo:$list[$id] : $vo;
		}
		$list=array_values($list);
		$arr1 = array_map(create_function('$n', 'return $n["create_time"];'), $list);
		array_multisort($arr1,SORT_DESC,SORT_NUMERIC,$list);
		foreach ($list as $key => $value) {
			$res['id'] = $value['id'];
			// $res['status'] = $value['status'];
			$res['type'] = $value['type'];
			$res['name'] = $this->doctor->where("user_id='".$value['member_id']."'")->getfield("name");
			$res['img_thumb'] = $this->doctor->where("user_id='".$value['member_id']."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$res['create_time'] = time_tran($value['create_time']);
			$res['member_id'] = $value['member_id'];
			$ress[] = $res;
		}
		//$count = $this->graphic->where("patientmember_id='".$uid."'")->count();
		//$ress['count'] = $count;

		if($ress)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $ress;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无数据";
			$data['info'] = array();
			outJson($data);
		}
		
	}
	//对此医生的多次咨询
	public function doctor_consult(){
		$uid = $this->uid;
		$member_id = I('post.member_id');
		// $member_id = 14;
		if(empty($member_id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "请选择医生！";
			outJson($data);
		}
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 10;
		$p=($p-1)*$limit;
		$result = $this->graphic->field("id,create_time,type,money,status")->where("member_id=".$member_id." and patientmember_id=".$uid)->order('create_time desc')->limit($p.",".$limit)->select();
		foreach ($result as $key => $value) {
			$res['id'] = $value['id'];
			$res['type'] = $value['type'];
			$res['status'] = $value['status'];
			$res['name'] = $this->doctor->where("user_id='".$member_id."'")->getfield("name");
			$res['img_thumb'] = $this->doctor->where("user_id='".$member_id."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$res['create_time'] = time_tran($value['create_time']);
			$ress[] = $res;
		}
		if($ress)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $ress;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无数据";
			$data['info'] = array();
			outJson($data);
		}

	}
}
?>