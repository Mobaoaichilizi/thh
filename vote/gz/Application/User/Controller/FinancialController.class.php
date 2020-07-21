<?php
// +----------------------------------------------------------------------
// | 收支明细接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class FinancialController extends UserbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->financial = D('Financial');
		$this->score = D('ScoreList');
		$this->setting = D('Setting');
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
	
	

public	function trading()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->financial->field('id,operation,content,money,recognizable,createtime')->where("user_id=".$uid." and recognizable=1")->order("createtime desc")->limit($p.','.$limit)->select();
		
		if($result){
			foreach ($result as &$value) {
				$value['createtime'] = date("Y-m-d H:i",$value['createtime']);
			}
			
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无信息！";
			$data['info'] = array();
			outJson($data);
		}
	}
	public	function score()
	{
		$uid = $this->uid;
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
		$p=($p-1)*$limit;
		$result = $this->score->field('id,score,content,setting_id,optime')->where("user_id=".$uid." and content='分'")->order("optime desc")->limit($p.','.$limit)->select();
		
		if($result){
			foreach ($result as &$value) {
				$value['optime'] = date("Y-m-d H:i",$value['optime']);
				$value['title'] = $this->setting->where("id=".$value['setting_id'])->getField("title");
			}
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $result;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 1;
			$data['message'] = "暂无信息！";
			$data['info'] = array();
			outJson($data);
		}
	}
	
}
?>