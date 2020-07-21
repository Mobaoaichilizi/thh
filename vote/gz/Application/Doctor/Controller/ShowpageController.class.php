<?php
// +----------------------------------------------------------------------
// | 展示主页接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\DoctorbaseController;
class ShowpageController extends DoctorbaseController {
	function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->member =D("Member");//医生信息表
        $this->reserve = D('Reserve');//预约就诊表
        $this->graphic = D('graphic');
        $this->telephone = D('telephone');
		$this->video = D('videodia');
		$this->consultation = D('Consultation');//坐诊时间表
        $this->user = D('User');
		$this->uid = $this->user->where("md5(md5(id))='".$uid."'")->getfield('id');
	}
	//验证医生个人信息
public	function show_page()
	{
        $id = $this->uid;
		$result1 = $this->member->field("id,name,img_thumb,profile,disease,graphic_price,telephone_price,reserve_price,video_price,address,professional,ranking,othercert,is_reserve,status")->where('user_id='.$id)->find();
        if($result1['address'] == NULL){
            $result1['address'] = '';
        }
        $due_time = $this->user->where("id=".$id)->getfield("due_time");
		$result1['level'] = $this->user->where("id=".$id)->getfield("member_level");
        $t = $due_time-time();
        if($t > 0){
            $result1['days_remaining'] = ceil($t/86400);
        }else{
            $result1['days_remaining'] = 0;
        }
        $doctor = $this->member->where('id='.$id)->find();
        $now = time();
        if($doctor['begin_time'] <= time() && $doctor['end_time'] >= time()){
            $result1['graphic_price'] = "0";
            $result1['telephone_price'] = "0";
            $result1['reserve_price'] = "0";
            $result1['video_price'] = "0";
            $result1['status'] = "4";
        }

        
        $num1 = $this->graphic->where("status=2 and member_id=".$id)->count();
        $num2 = $this->telephone->where("status=2 and member_id=".$id)->count();
        $num3 = $this->reserve->where("status=2 and member_id=".$id)->count();
        $num4 = $this->video->where("status=2 and member_id=".$id)->count();
		$result1['service_number'] = $num1 + $num2 + $num3 + $num4;
        $result1['othercert'] = explode(",", $result1['othercert']);
		$result2 = $this->consultation->where("doctor_id=".$id)->find();
		$week = get_week_date();
		foreach($week as $k=>$v)
    {
        if($v['week']=="星期一")
        {
            $week[$k]['morning_number'] = $result2['mon_morning'];
            $week[$k]['afternoon_number'] = $result2['mon_afternoon'];
            $week[$k]['week_id'] = 1;
        }
        else if($v['week']=="星期二")
        {
            $week[$k]['morning_number'] = $result2['tues_morning'];
            $week[$k]['afternoon_number'] = $result2['tues_afternoon'];
            $week[$k]['week_id'] = 2;

        }
        else if($v['week']=="星期三")
        {
            $week[$k]['morning_number'] = $result2['wed_morning'];
            $week[$k]['afternoon_number'] = $result2['wed_afternoon'];
            $week[$k]['week_id'] = 3;
        }
        else if($v['week']=="星期四")
        {
            $week[$k]['morning_number'] = $result2['thur_morning'];
            $week[$k]['afternoon_number'] = $result2['thur_afternoon'];
            $week[$k]['week_id'] = 4;
        }
        else if($v['week']=="星期五")
        {
            $week[$k]['morning_number'] = $result2['fri_morning'];
            $week[$k]['afternoon_number'] = $result2['fri_afternoon'];
            $week[$k]['week_id'] = 5;
        }
        else if($v['week']=="星期六")
        {
            $week[$k]['morning_number'] = $result2['sat_morning'];
            $week[$k]['afternoon_number'] = $result2['sat_afternoon'];
            $week[$k]['week_id'] = 6;
        }
        else if($v['week']=="星期日")
        {
            $week[$k]['morning_number'] = $result2['sun_morning'];
            $week[$k]['afternoon_number'] = $result2['sun_afternoon'];
            $week[$k]['week_id'] = 7;
        }
    }
    $result1['week'] = array_values($week);
		
	
    

		
		if($result1){
			unset($data);
			$data['code'] = 1;
			$data['message'] = "信息加载成功！";
			$data['info'] = $result1;
			outJson($data);
		}else{
			unset($data);
			$data['code'] = 0;
			$data['message'] = "数据加载失败！";
			outJson($data);
		}
	}

}
?>