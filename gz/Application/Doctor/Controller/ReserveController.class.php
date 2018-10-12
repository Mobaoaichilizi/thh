<?php
// +----------------------------------------------------------------------
// | 预约就诊列表接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class ReserveController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->reserve =D("Reserve");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield('id');
	}

public	function reserve_list()
	{
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->reserve->field("id,member_id,patientmember_id,status,type,money,create_time")->where("member_id='".$id."' and status in (1,2)")->order("id desc")->limit($p.",".$limit)->select();
		foreach ($result as $key => $value) {
			$res['id'] = $value['id'];
			$res['status'] = $value['status'];
			$res['create_time'] = time_tran($value['create_time']);
			$res['type'] = $value['type'];
			$res['name'] = $this->patient->where("user_id='".$value['patientmember_id']."'")->getfield("name");
			$res['img_thumb'] = $this->patient->where("user_id='".$value['patientmember_id']."'")->getfield("img_thumb");
			$res['price'] = $value['money'];
			$ress[] = $res;
		}
		$count = $this->reserve->where("member_id='".$id."'")->count();
		// $ress['count'] = $count;
		if(empty($ress)){
			$ress=array();
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
			$data['message']="暂无数据！";
			$data['info'] = array();
			outJson($data);
		}
		
	}
	public function is_open(){
		$uid = $this->uid;
		$is_reserve = I('is_reserve');
		$res = array(
			"is_reserve" => $is_reserve,
		);
		$result = $this->doctor->where("user_id=".$uid)->save($res);
		unset($data);
		$data['code']=1;
		$data['message']="设置成功！";
		outJson($data);
		

	}

}
?>