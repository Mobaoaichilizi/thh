<?php
// +----------------------------------------------------------------------
// | 图文咨询列表接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class GraphicController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->graphic =D("Graphic");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield('id');
	}

public	function graphic_list()
	{
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		
		$p=($p-1)*$limit;
		$result = $this->graphic->field("id,member_id,patientmember_id,status,create_time,type,money")->where("member_id='".$id."' and status in (1,2)")->order("id desc")->limit($p.",".$limit)->select();
		foreach ($result as $key => $value) {
			$res['id'] = $value['id'];
			$res['status'] = $value['status'];
			$res['type'] = $value['type'];
			$res['create_time'] = time_tran($value['create_time']);
			$res['name'] = $this->patient->where("user_id='".$value['patientmember_id']."'")->getfield("name");
			$res['img_thumb'] = $this->patient->where("user_id='".$value['patientmember_id']."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$ress['content'][] = $res;
		}
		$count = $this->graphic->where("member_id='".$id."'")->count();
		$ress['count'] = $count;
		if(empty($ress['content'])){
			$ress['content']=array();
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
			$data['info']=array();
			outJson($data);
		}
		
	}

}
?>