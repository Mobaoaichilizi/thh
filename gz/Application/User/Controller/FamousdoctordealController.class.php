<?php
// +----------------------------------------------------------------------
// | 名医工作室列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class FamousdoctordealController extends UserbaseController {
	public function _initialize() {
		$uid=$_REQUEST['uid'];
		$deviceid=$_REQUEST['deviceid'];
		accessDeviceidCheck($uid,$deviceid);
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->graphic =D("Graphic");
		$this->reserve = D('Reserve');//预约咨询表
		$this->telephone = D('Telephone');//电话咨询表
		$this->videodia = D('Videodia');//视频咨询表
		$this->consultation = D('Consultation');//坐诊时间表
		$this->exclusive = D('Exclusive');//专属医生表
		$this->uid=$this->user->where("md5(md5(id))='".$uid."'")->getField('id');
		
	}
	//名医工作室
	public function deal()
	{
		$uid = $this->uid;
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择医生";
			outJson($data);	
		}
		
		$result1 = $this->member->field("user_id,name,img_thumb,profile,graphic_price,telephone_price,reserve_price,video_price,address,professional,ranking,othercert,disease,is_reserve")->where('user_id='.$id)->find();
		$count = $this->exclusive->where("patient_id=".$uid." and doctor_id=".$id)->count();
		if($count>0){
			$result1['exclusive'] = 1;
		}else{
			$result1['exclusive'] = 0;
		}
		$result1['doctor_id'] = md5(md5($id));
		if($result1){
			$result1['level']=$this->user->where("id=".$result1['user_id'])->getField('member_level');
			if($result1['level'] == 0){
			$result1['days_remaining'] = 0;
			}
			$result1['othercert']=explode(',',$result1['othercert']);
			
			$number1 = $this->graphic->where("member_id=".$id)->getfield('patientmember_id',true);
			$number_str = implode(',', $number1);
			$number2 = $this->telephone->where("member_id=".$id)->getfield('patientmember_id',true);
			$number2_str = implode(',', $number2);
			$number3 = $this->reserve->where("member_id=".$id)->getfield('patientmember_id',true);
			$number3_str = implode(',', $number3);
			$number_str.=",".$number2_str.",".$number3_str;
			$number = explode(',', $number_str);
			$number  = array_unique($number);
			$length = count($number);
			$result1['service_number'] = $length;
			$result2 = $this->consultation->where('doctor_id='.$id)->find();
			$week = get_week_date();
			foreach($week as $k=>$v)
			{
				
					//$morning_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '上午'";
				$morning_count = $this->telephone->where("member_id=".$id." and tel_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=0")->count();
				
				//$afternoon_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '下午'";
				$afternoon_count = $this->telephone->where("member_id=".$id." and tel_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=1")->count();
				
				//$morning_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '上午'";
				$morningreserve_count = $this->reserve->where("member_id=".$id." and res_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=0")->count();
				
				//$afternoon_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '下午'";
				$afternoonreserve_count = $this->reserve->where("member_id=".$id." and res_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=1")->count();
				
				//$morning_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '上午'";
				$morningvideodia_count = $this->videodia->where("member_id=".$id." and video_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=0")->count();
				
				//$afternoon_sql = "select id from cool_tel_consultation where doctor_id='".$id."' and date= '".$v['date']."' and times= '下午'";
				$afternoonvideodia_count = $this->videodia->where("member_id=".$id." and video_date='".strtotime($v['year'].'-'.$v['date'])."' and morn_after=1")->count();
				
				if($v['week']=="星期一")
				{
					$week[$k]['morning_number'] = $result2['mon_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['mon_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 1;
				}
				else if($v['week']=="星期二")
				{
					$week[$k]['morning_number'] = $result2['tues_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['tues_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 2;

				}
				else if($v['week']=="星期三")
				{
					$week[$k]['morning_number'] = $result2['wed_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['wed_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 3;
				}
				else if($v['week']=="星期四")
				{
					$week[$k]['morning_number'] = $result2['thur_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['thur_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 4;
				}
				else if($v['week']=="星期五")
				{
					$week[$k]['morning_number'] = $result2['fri_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['fri_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 5;
				}
				else if($v['week']=="星期六")
				{
					$week[$k]['morning_number'] = $result2['sat_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['sat_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 6;
				}
				else if($v['week']=="星期日")
				{
					$week[$k]['morning_number'] = $result2['sun_morning']-$morning_count-$morningreserve_count-$morningvideodia_count;
					$week[$k]['afternoon_number'] = $result2['sun_afternoon']-$afternoon_count-$afternoonreserve_count-$afternoonvideodia_count;
					$week[$k]['week_id'] = 7;
				}
			}
			$result1['week'] = array_values($week);
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