<?php
// +----------------------------------------------------------------------
// | 编辑主页接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class EditpageController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member =D("Member");
		$this->reserve =D("Reserve");
		$this->consultation = D('Consultation');
		$this->uid=D('User')->where("md5(md5(id))='".$uid."'")->getField('id');
	}
public function page_info(){
		$id = $this->uid;
		$result1 = $this->member->where('user_id='.$id)->find();
		$result2 = $this->consultation->where('doctor_id='.$id)->find();
		$result3 = $this->reserve->field('address')->where('member_id='.$id)->find();
		$res = array_merge($result1,$result2,$result3);
		if($res){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $res;
			outJson($data);
		}
	}	
public	function edit_page()
	{
		$id = $this->uid;
		
		$profile = I('post.profile');
		$disease = I('post.disease');
		$othercert = I('post.othercert');
		$address = I('post.address');
		$graphic_price = I('post.graphic_price');
		$telephone_price = I('post.telephone_price');
		$is_reserve  = I('post.is_reserve');
		$reserve_price = I('post.reserve_price');
		$video_price = I('post.video_price');
		$mon_morning = I('mon_morning',10);
		$mon_afternoon = I('mon_afternoon',10);
		$tues_morning = I('tues_morning',10);
		$tues_afternoon = I('tues_afternoon',10);
		$wed_morning = I('wed_morning',10);
		$wed_afternoon = I('wed_afternoon',10);
		$thur_morning = I('thur_morning',10);
		$thur_afternoon = I('thur_afternoon',10);
		$fri_morning = I('fri_morning',10);
		$fri_afternoon = I('fri_afternoon',10);
		$sat_morning = I('sat_morning',10);
		$sat_afternoon = I('sat_afternoon',10);
		$sun_morning = I('sun_morning',10);
		$sun_afternoon = I('sun_afternoon',10);
		if(empty($address))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请输入坐诊地址！";
			outJson($data);
		}
		$res3 = array(
			'mon_morning' => $mon_morning,
			'mon_afternoon' => $mon_afternoon,
			'tues_morning' => $tues_morning,
			'tues_afternoon' => $tues_afternoon,
			'wed_morning' => $wed_morning,
			'wed_afternoon' => $wed_afternoon,
			'thur_morning' => $thur_morning,
			'thur_afternoon' => $thur_afternoon,
			'fri_morning' => $fri_morning,
			'fri_afternoon' => $fri_afternoon,
			'sat_morning' => $sat_morning,
			'sat_afternoon' => $sat_afternoon,
			'sun_morning' => $sun_morning,
			'sun_afternoon' => $sun_afternoon,
		);
		$resultss = $this->consultation->where('doctor_id='.$id)->save($res3);
		$res4 = array(
			'graphic_price'=> $graphic_price,
			'telephone_price'=> $telephone_price,
			'reserve_price'=> $reserve_price,
			'video_price'=> $video_price,
			'othercert' => $othercert,
			'profile'  => $profile,
			'disease' => $disease,
			'address'=> $address,
			'is_reserve'=> $is_reserve,
		);
		$results = $this->member->where('user_id='.$id)->save($res4);
			if($resultss!==false){
				if($results!==false)
				{
					unset($data);
					$data['code']="1";
					$data['message']="信息保存成功！";
					$data['info']=$results;
					outJson($data);
				}else
				{
					unset($data);
					$data['code']=0;
					$data['message']="信息保存失败！";
					outJson($data);
				}
			}else{
				unset($data);
				$data['code']=0;
				$data['message']="添加坐诊时间失败！";
				outJson($data);
			}
		}
		
	
	

}
?>