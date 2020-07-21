<?php
// +----------------------------------------------------------------------
// | 寻医诊疗列表管理文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace User\Controller;
use Common\Controller\UserbaseController;
class TreatmentController extends UserbaseController {
	public function _initialize() {
		parent::_initialize();
		$this->user =D("User");
		$this->member =D("Member");
		$this->department =D("Department");
		$this->goods =D("Goods");
		$this->reserve = D('Reserve');//预约就诊表
		$this->consultation = D('Consultation');//坐诊时间表
		$this->adv = D('Adv');//坐诊时间表
		
	}
	//名医工作室列表
	public function lists()
	{
		
		$p=!empty($_POST['p']) ? $_POST['p'] : 1;
		$limit=!empty($_POST['limit']) ? $_POST['limit'] : 0;
	
		$department_id=!empty($_POST['department_id']) ? $_POST['department_id'] : 0;
	
		$longitude=!empty($_POST['longitude']) ? $_POST['longitude'] : 0;
	
		$latitude=!empty($_POST['latitude']) ? $_POST['latitude'] : 0;
	
		$typesort=!empty($_POST['typesort']) ? $_POST['typesort'] : 0;
		
		$default_id=!empty($_POST['default_id']) ? $_POST['default_id'] : 0;
		$p=($p-1)*$limit;
		
		if($department_id!==0)
		{
			$sqlwhere.=" and lgq_member.department_id=".$department_id;
		}
		if($typesort==1)
		{
			$orderwhere="juli asc,";
		}
		$result=$this->member->join('lgq_user on lgq_member.user_id=lgq_user.id','left')->join('lgq_hospital on lgq_member.hospital_id=lgq_hospital.id','left')->field("lgq_member.user_id as id,lgq_member.name as name,lgq_member.img_thumb as img_thumb,lgq_member.professional as professional,lgq_member.profile as profile,lgq_user.member_level as level,lgq_user.is_login as is_login,ROUND(6378.138 * 2 * ASIN(SQRT(POW(SIN((".$latitude." * PI() / 180 - lgq_hospital.lat * PI() / 180) / 2),2) + COS(".$latitude." * PI() / 180) * COS(lgq_hospital.lat * PI() / 180) * POW(SIN((".$longitude." * PI() / 180 - lgq_hospital.lng * PI() / 180) / 2),2))) * 1000) AS juli")->where("lgq_member.status=1 and lgq_user.role=1 ".$sqlwhere)->order($orderwhere."lgq_user.member_level desc,lgq_user.is_login asc,lgq_user.createtime desc")->limit($p.",".$limit)->select();
		if($result)
		{
			foreach($result as &$row)
			{
				$row['juli']=round($row['juli']/1000,2);	
			}
			unset($data);
			$data['code']=1;
			$data['message']="获取医生列表成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无医生！";
			$data['info']=array();
			outJson($data);	
		}
	}
	public function banner()
	{
		$result=$this->adv->field("title,img_thumb")->where("adv_id=206")->order('sort asc')->select();
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="banner图获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=2;
			$data['message']="暂无图片！";
			outJson($data);	
		}
	}
	//科室分类
	public function departmentlist()
	{
		$result=$this->department->field("id,name,img_thumb")->where("status=1 and type=1")->order('sort asc')->select();
		
		if($result)
		{
			foreach($result as &$val)
			{
				$val['img_thumb']=$this->host.$val['img_thumb'];
			}
			unset($data);
			$data['code']=1;
			$data['message']="科室分类获取成功！";
			$data['info']=$result;
			outJson($data);	
		}else
		{
			unset($data);
			$data['code']=1;
			$data['message']="暂无分类！";
			$data['info']=array();
			outJson($data);	
		}
		
	}
	
	//名医工作室列表
	public function deal()
	{
		$id=I('post.id');
		if(empty($id))
		{
			unset($data);
			$data['code']=0;
			$data['message']="请选择医生";
			outJson($data);	
		}
		$result1 = $this->member->field("name,age,user_id,img_thumb,profile,graphic_price,telephone_price,reserve_price,video_price,address,professional,ranking,othercert,disease,is_reserve")->where('user_id='.$id)->find();
		if($result1){
			//$result1['phone']=str_pad_star($this->user->where("id=".$result1['user_id'])->getField('username'),7,'****','left');
			$result1['level']=$this->user->where("id=".$result1['user_id'])->getField('member_level');
			if($result1['level'] == 0){
			$result1['days_remaining'] = 0;
			}
			$result1['othercert']=explode(',',$result1['othercert']);
			$result1['service_number'] = 85;
			$result2 = $this->consultation->where('doctor_id='.$id)->find();
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