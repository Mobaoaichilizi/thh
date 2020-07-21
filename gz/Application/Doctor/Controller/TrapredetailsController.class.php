<?php
// +----------------------------------------------------------------------
// | 传统处方详情接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class TrapreController extends DoctorbaseController {
	function _initialize() {
		$token=$_REQUEST['token'];
		accessTokenCheck($token);//身份验证
		parent::_initialize();
		$this->pre =D("Pre");
		$this->personaddress =D("Personaddress");
		$this->patient =D("Patientmember");
		
	}
public function pre_details(){
		$id = I('post.id');
		$results = $this->pre->field("patient_id,personaddress_id,img_thumb_common,img_thumb_prime,pre_bz")->where("id = '".$id."' and setting_id_class = 44")->find();
		if(empty($results['patient_id'])){
			if(empty($results['personaddress_id'])){
				unset($data);
				$data['code'] = 0;
				$data['message'] = "患者信息加载错误！";
				outJson($data);
			}else{
				$result = $this->personaddress->where("id=".$personaddress_id)->find();
				if($result){
					$results['patient'] = $result;
					unset($data);
					$data['code'] = 0;
					$data['message'] = "加载成功！";
					$data['info'] = $results;
					outJson($data);
				}else{
					unset($data);
					$data['code'] = 0;
					$data['message'] = "患者信息加载错误！";
					outJson($data);
				}
			}
		}else{
			$result = $this->patient->where("user_id=".$patient_id)->find();
			if($result){
					$results['patient'] = $result;
					unset($data);
					$data['code'] = 0;
					$data['message'] = "加载成功！";
					$data['info'] = $results;
					outJson($data);
				}else{
					unset($data);
					$data['code'] = 0;
					$data['message'] = "患者信息加载错误！";
					outJson($data);
				}
		}

	}
}
?>