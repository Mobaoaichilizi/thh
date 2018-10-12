<?php
// +----------------------------------------------------------------------
// | 图文咨询详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class GraphicdetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->graphic =D("Graphic");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->user = D("User");
		$this->uid = $this->user->where("md5(md5(id))='".$uid."'")->getfield("id");
	}

public	function graphic_details()
	{
		$uid = $this->uid;//用户的ID
		$id = I('post.id');//图文咨询的ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$result = $this->graphic->field("patientmember_id,description,status,money,img_thumb as img_desc")->where("id='".$id."'")->find();
			$patient = $this->patient->field("user_id,name,img_thumb,phone")->where("user_id='".$result['patientmember_id']."'")->find();
			
			$results['id'] = $id;
			$results['name'] = $patient['name'];
			$results['user_id'] = $patient['user_id'];
			$results['img_thumb'] = $patient['img_thumb'];
			$results['phone'] = $this->user->where("id=".$patient['user_id'])->getfield("username");
			// $results['phone'] = str_pad_star($patient['phone'],7,'****','left');
			$results['description'] = $result['description'];
			$results['img_desc'] = $result['img_desc'];
			$results['status'] = $result['status'];
			$results['price'] = $result['money'];
			
		
		if($results)
		{
			unset($data);
			$data['code']=1;
			$data['message']="信息加载成功！";
			$data['info'] = $results;
			outJson($data);
		}else
		{
			unset($data);
			$data['code']=0;
			$data['message']="信息加载失败！";
			outJson($data);
		}
		
	}
//退还诊费
public function exit_pay(){
		$id = I('post.id');
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "没有选择的咨询！";
			outJson($data);
		}
		$uid = $this->uid;
		$create_time = $this->graphic->where("id=".$id)->getfield("create_time");
		$status = $this->graphic->where("id=".$id)->getfield("status");
		$is_replay = $this->graphic->where("id=".$id)->getfield("is_replay");
		$ress = $this->graphic->where("id=".$id)->getfield("money");
		if(!is_numeric($create_time)){

	        $time=strtotime($create_time);

	    }
	    $t = time()-$create_time;
	    if($t > 86400){
	    	if($status == 1 && $is_replay == 0){
	    		$res = array(
	    			'status' => 3,	
	    		);
	    		$result = $this->graphic->where('id='.$id)->save($res);
	    		if($result){
	    			unset($data);
					$data['code'] = 1;
					$data['message'] = "自动退还成功！";
					$data['pay'] = $ress;
					outJson($data);
	    		}else{
	    			unset($data);
					$data['code'] = 0;
					$data['message'] = "自动退还失败！";
					outJson($data);
	    		}
	    	}
	    }
	    if($status == 1 && $is_replay == 0){
	    	$res = array(
			'status' => 4,
			);
			$result = $this->graphic->where('id='.$id)->save($res);
			
			if($result){
				unset($data);
				$data['code'] = 1;
				$data['message'] = "退还成功！";
				$data['pay'] = $ress;
				outJson($data);
			}else{
				unset($data);
				$data['code'] = 0;
				$data['message'] = "退还失败！";
				outJson($data);
			}
	    }else{
	    	unset($data);
			$data['code'] = 0;
			$data['message'] = "不支持退还！";
			outJson($data);
	    }
		
	}
public function exit_consult()
	{
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 2,
		);
		$result = $this->graphic->where('id='.$id)->save($res);
		if($result){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "结束成功！";
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "结束失败！";
			outJson($data);
		}
	}
}
?>