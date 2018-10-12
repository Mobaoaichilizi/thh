<?php
// +----------------------------------------------------------------------
// | 奖励明细接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class ScoreController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
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
		$where['setting_id'] = array('not in','166,207');
		$where['user_id'] = array('eq',$uid);
		$result = $this->score->field('id,score,content,setting_id,optime')->where($where)->order("optime desc")->limit($p.','.$limit)->select();
		
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