<?php
// +----------------------------------------------------------------------
// | 我发起的在线会诊管理接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class MyonlineController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member = D('Member');
		$this->onlinetreat =D("Onlinetreat");
		$this->onlinetreatuser =D("Onlinetreatuser");
		$this->uid = D('User')->where("md5(md5(id))='".$uid."'")->getfield("id");
	}
//我发起的会诊室信息
public	function myonline_list()
	{
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		
		$p=($p-1)*$limit;
		$result = $this->onlinetreatuser->where("user_id='".$id."'")->limit($p.",".$limit)->select();
		if(empty($result)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "暂无信息！";
			outJson($data);
		}

		foreach ($result as $key => $value) {
			
			$value['user'] = $this->member->field('name,img_thumb')->where("user_id = '".$value['user_id']."'")->find();
			if(!empty($value['join_user'])){
				$value['join_user'] = $this->member->field('name,img_thumb')->where("user_id in (".$value['join_user'].")")->select();
			}
			$ress = $this->onlinetreat->field('status,create_time')->where("id='".$value['online_id']."'")->find();
			$value['create_time'] = format_date($ress['create_time']);
			if($ress['status'] == 2){
				$res['diaed'][] = $value; 
			}else if($ress['status'] == 1){
				$res['dia'][] = $value;
			}
		}
		if($res)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $res;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息加载失败！";
			outJson($data);
		}
		
	}
//我加入的会诊室
public function joinonline_list(){
		$id = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 0;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->onlinetreatuser->where("'".$id."' = join_user or join_user like '".$id.",%' or join_user like '%".$id."%'")->limit($p.",".$limit)->select();

		foreach ($result as $key => $value) {
				$value['user'] = $this->member->field('name,img_thumb')->where("user_id = '".$value['user_id']."'")->find();
				if(!empty($value['join_user'])){
					$value['join_user'] = $this->member->field('name,img_thumb')->where("user_id in (".$value['join_user'].")")->select();
				}
				$ress = $this->onlinetreat->field('status,create_time')->where("id='".$value['online_id']."'")->find();
				$value['create_time'] = format_date($ress['create_time']);

				if($ress['status'] == 2){
					$res['diaed'][] = $value; 
				}else if($ress['status'] == 1){
					$res['dia'][] = $value;
				}
			}
		if($res){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $res;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "信息加载失败！";
			outJson($data);
		}
}

}
?>