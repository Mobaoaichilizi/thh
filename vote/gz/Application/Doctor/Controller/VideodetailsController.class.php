<?php
// +----------------------------------------------------------------------
// | 视频诊疗详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class VideodetailsController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->video =D("Videodia");
		$this->patient =D("Patientmember");
		$this->doctor =D("Member");
		$this->user =D("User");
		$this->uid = D("User")->where("md5(md5(id))='".$uid."'")->getfield('id');
	}

public	function video_details()
	{
		$uid = $this->uid;//用户的ID
		$id = I('post.id');//预约诊疗的ID
		if(empty($id)){
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据错误！";
			outJson($data);
		}
		$result = $this->video->field("id,patientmember_id,video_date,money,morn_after,status")->where("id='".$id."'")->find();
		$patient = $this->patient->field("user_id,name,img_thumb")->where("user_id='".$result['patientmember_id']."'")->find();
		$results['id'] = $id;
		$results['name'] = $patient['name'];
		$results['user_id'] = $patient['user_id'];
		$results['img_thumb'] = $patient['img_thumb'];
		$results['phone'] = $this->user->where("id=".$result['patientmember_id'])->getfield('username');
		$results['morn_after'] = $result['morn_after'];
		$results['status'] = $result['status'];
		$results['price'] = $result['money'];
		$results['video_date'] = date('Y年m月d日',$result['video_date']);
		$results['week'] = weekday($result['video_date']);


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
		$create_time = $this->video->where("id=".$id)->getfield("create_time");
		$status = $this->video->where("id=".$id)->getfield("status");
		$is_replay = $this->video->where("id=".$id)->getfield("is_replay");
		$ress = $this->video->where("id=".$id)->getfield("money");
		$patient = $this->video->where("id=".$id)->getfield("patientmember_id");
		if(!is_numeric($create_time)){

	        $time=strtotime($create_time);

	    }
	    $t = time()-$create_time;
	    if($t > 86400){
	    	if($status == 1 && $is_replay == 0){
	    		$res = array(
	    			'status' => 3,	
	    		);
	    		$result = $this->video->where('id='.$id)->save($res);
	    		if($result){
	    			$results = $this->user->where('id='.$patient)->setInc('balance',$ress);
	    			$balance = $this->user->where('id='.$patient)->getField('balance');
	    			financial_log($patient,$ress,3,$balance,'视频咨询退款',4);
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
			$result = $this->video->where('id='.$id)->save($res);
			
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
//结束咨询
public function exit_consult(){
		$id = I('post.id');
		$uid = $this->uid;
		$res = array(
			'status' => 2,
		);
		$result = $this->video->where('id='.$id)->save($res);
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