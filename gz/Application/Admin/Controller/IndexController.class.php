<?php
// +----------------------------------------------------------------------
// | 后台首页文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class IndexController extends AdminbaseController {
    public function index(){
		$admin=M('admin');
		$admin_id=session('admin_id');
		$admin_list=$admin->where('id='.$admin_id)->find();
		$this->assign('admin_list',$admin_list);
        $this->display('index');
    }
	public function welcome()
	{
		$admin=M('admin');
		$admin_id=session('admin_id');
		$this->user = M('User');
		$this->forum = M('Forum');
		$this->order = M('Order');
		$this->graphic = M('Graphic');
		$this->telephone = M('Telephone');
		$this->reserve = M('Reserve');
		$this->video = M('Videodia');
		$this->pre = M('Pre');
		$this->pharmacy = M('Pharmacy');
		
		$is_pharmacy = $admin->where("id=".$admin_id)->getfield("is_pharmacy");
		
		// 今日新增医生数
		$today_doctor_count=$this->user->where("role = 1 and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		// 今日新增患者数
		$today_patient_count=$this->user->where("role = 2 and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		//今日新增论坛数
		$today_forum_count=$this->forum->where("create_time > ".strtotime(date("Y-m-d"))." and create_time < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		//今日新增订单数
		$today_ticket_count=$this->order->where("createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		//今日新增图文咨询
		$today_graphic_count=$this->graphic->where("create_time > ".strtotime(date("Y-m-d"))." and create_time < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		//今日新增电话咨询
		$today_telephone_count=$this->telephone->where("create_time > ".strtotime(date("Y-m-d"))." and create_time < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		//今日新增预约咨询
		$today_reserve_count=$this->reserve->where("create_time > ".strtotime(date("Y-m-d"))." and create_time < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		
		//今日新增处方数
		$today_video_count=$this->pre->where("create_time > ".strtotime(date("Y-m-d"))." and create_time < ".(strtotime(date("Y-m-d"))+3600*24))->count();

		//今日各大药房新增处方数
		$today_pre_count=$this->pre->where("create_time > ".strtotime(date("Y-m-d"))." and create_time < ".(strtotime(date("Y-m-d"))+3600*24)." and admin_pre=".$admin_id)->count();


		



		$graphic_count=$this->graphic->count();
		$telephone_count=$this->telephone->count();
		$reserve_count=$this->reserve->count();
	
		$var=time();
		
		$date=date("d",$var);
		$date1 = date("d",strtotime("-1 day"));    
		$date2 = date("d",strtotime("-2 day"));    
		$date3 = date("d",strtotime("-3 day"));    
		$date4 = date("d",strtotime("-4 day"));    
		$date5 = date("d",strtotime("-5 day"));    
		$date6 = date("d",strtotime("-6 day"));  

		$month=date("m",$var);
		// $month1 = date("m",strtotime("-1 month"));
		$month1 = date("m", strtotime('midnight first day of -1 month'));
		$month2 = date("m",strtotime("midnight first day of -2 month"));    
		$month3 = date("m",strtotime("midnight first day of -3 month"));    
		$month4 = date("m",strtotime("midnight first day of -4 month"));    
		$month5 = date("m",strtotime("midnight first day of -5 month"));    
		$month6 = date("m",strtotime("midnight first day of -6 month"));   
		$month7 = date("m",strtotime("midnight first day of -7 month"));   
		$month8 = date("m",strtotime("midnight first day of -8 month"));   
		$month9 = date("m",strtotime("midnight first day of -9 month"));   
		$month10 = date("m",strtotime("midnight first day of -10 month"));   
		$month11 = date("m",strtotime("midnight first day of -11 month"));   
		// 今日新增会员数
		$today_member_count=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d"))." and createtime < ".(strtotime(date("Y-m-d"))+3600*24))->count();
		$today_member_count1=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d",strtotime("-1 day")))." and createtime < ".(strtotime(date("Y-m-d",strtotime("-1 day")))+3600*24))->count();
		$today_member_count2=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d",strtotime("-2 day")))." and createtime < ".(strtotime(date("Y-m-d",strtotime("-2 day")))+3600*24))->count();
		$today_member_count3=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d",strtotime("-3 day")))." and createtime < ".(strtotime(date("Y-m-d",strtotime("-3 day")))+3600*24))->count();
		$today_member_count4=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d",strtotime("-4 day")))." and createtime < ".(strtotime(date("Y-m-d",strtotime("-4 day")))+3600*24))->count();
		$today_member_count5=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d",strtotime("-5 day")))." and createtime < ".(strtotime(date("Y-m-d",strtotime("-5 day")))+3600*24))->count();
		$today_member_count6=$this->user->where("member_level!=0 and createtime > ".strtotime(date("Y-m-d",strtotime("-6 day")))." and createtime < ".(strtotime(date("Y-m-d",strtotime("-6 day")))+3600*24))->count();

		$a = date("Y-m-01",time());
		// $a1 = date("Y-m-01",strtotime("-1 month"));
		// $a1 = date("Y-m-01", strtotime(date("Y-m-01") – 86400);
		$a1 = date("Y-m-01", strtotime('midnight first day of -1 month'));
		$a2 = date("Y-m-01",strtotime("midnight first day of -2 month"));
		$a3 = date("Y-m-01",strtotime("midnight first day of -3 month"));
		$a4 = date("Y-m-01",strtotime("midnight first day of -4 month"));
		$a5 = date("Y-m-01",strtotime("midnight first day of -5 month"));
		$a6 = date("Y-m-01",strtotime("midnight first day of -6 month"));
		$a7 = date("Y-m-01",strtotime("midnight first day of -7 month"));
		$a8 = date("Y-m-01",strtotime("midnight first day of -8 month"));
		$a9 = date("Y-m-01",strtotime("midnight first day of -9 month"));
		$a10 = date("Y-m-01",strtotime("midnight first day of -10 month"));
		$a11 = date("Y-m-01",strtotime("midnight first day of -11 month"));

		$thismonth_start = strtotime(date("Y-m-01",time()));
     	$thismonth_end = strtotime(date("Y-m-d",strtotime("$a +1 month -1 day")));

		// $thismonth_start=mktime(0,0,0,date('m'),1,date('Y'));
		// $thismonth_end=mktime(23,59,59,date('m'),date('t'),date('Y'));
		$thismonth_start1 = strtotime(date("Y-m-01",strtotime("-1 month")));
		
		$thismonth_end1 = strtotime(date("Y-m-d",strtotime("$a1 +1 month -1 day")));
		$thismonth_start2 = strtotime(date("Y-m-01",strtotime("-2 month")));
		$thismonth_end2 = strtotime(date("Y-m-d",strtotime("$a2 +1 month -1 day")));
		$thismonth_start3 = strtotime(date("Y-m-01",strtotime("-3 month")));
		$thismonth_end3 = strtotime(date("Y-m-d",strtotime("$a3 +1 month -1 day")));
		$thismonth_start4 = strtotime(date("Y-m-01",strtotime("-4 month")));
		$thismonth_end4 = strtotime(date("Y-m-d",strtotime("$a4 +1 month -1 day")));
		$thismonth_start5 = strtotime(date("Y-m-01",strtotime("-5 month")));
		$thismonth_end5 = strtotime(date("Y-m-d",strtotime("$a5 +1 month -1 day")));
		$thismonth_start6 = strtotime(date("Y-m-01",strtotime("-6 month")));
		$thismonth_end6 = strtotime(date("Y-m-d",strtotime("$a6 +1 month -1 day")));
		$thismonth_start7 = strtotime(date("Y-m-01",strtotime("-7 month")));
		$thismonth_end7 = strtotime(date("Y-m-d",strtotime("$a7 +1 month -1 day")));
		$thismonth_start8 = strtotime(date("Y-m-01",strtotime("-8 month")));
		$thismonth_end8 = strtotime(date("Y-m-d",strtotime("$a8 +1 month -1 day")));
		$thismonth_start9 = strtotime(date("Y-m-01",strtotime("-9 month")));
		$thismonth_end9 = strtotime(date("Y-m-d",strtotime("$a9 +1 month -1 day")));
		$thismonth_start10 = strtotime(date("Y-m-01",strtotime("-10 month")));
		$thismonth_end10 = strtotime(date("Y-m-d",strtotime("$a10 +1 month -1 day")));
		$thismonth_start11 = strtotime(date("Y-m-01",strtotime("-11 month")));
		$thismonth_end11 = strtotime(date("Y-m-d",strtotime("$a11 +1 month -1 day")));
		//今月新增药方数
		$today_pharmacy_count=$this->pharmacy->where("create_time > ".$thismonth_start." and create_time < ".$thismonth_end)->count();
		
		$today_pharmacy_count1=$this->pharmacy->where("create_time > ".$thismonth_start1." and create_time < ".$thismonth_end1)->count();
		$today_pharmacy_count2=$this->pharmacy->where("create_time > ".$thismonth_start2." and create_time < ".$thismonth_end2)->count();
		$today_pharmacy_count3=$this->pharmacy->where("create_time > ".$thismonth_start3." and create_time < ".$thismonth_end3)->count();
		$today_pharmacy_count4=$this->pharmacy->where("create_time > ".$thismonth_start4." and create_time < ".$thismonth_end4)->count();
		$today_pharmacy_count5=$this->pharmacy->where("create_time > ".$thismonth_start5." and create_time < ".$thismonth_end5)->count();
		$today_pharmacy_count6=$this->pharmacy->where("create_time > ".$thismonth_start6." and create_time < ".$thismonth_end6)->count();
		$today_pharmacy_count7=$this->pharmacy->where("create_time > ".$thismonth_start7." and create_time < ".$thismonth_end7)->count();
		$today_pharmacy_count8=$this->pharmacy->where("create_time > ".$thismonth_start8." and create_time < ".$thismonth_end8)->count();
		$today_pharmacy_count9=$this->pharmacy->where("create_time > ".$thismonth_start9." and create_time < ".$thismonth_end9)->count();
		$today_pharmacy_count10=$this->pharmacy->where("create_time > ".$thismonth_start10." and create_time < ".$thismonth_end10)->count();
		$today_pharmacy_count11=$this->pharmacy->where("create_time > ".$thismonth_start11." and create_time < ".$thismonth_end11)->count();
		
		$this->assign("is_pharmacy",$is_pharmacy);
		$this->assign("today_doctor_count",$today_doctor_count);
		$this->assign("today_patient_count",$today_patient_count);
		$this->assign("today_forum_count",$today_forum_count);
		$this->assign("today_ticket_count",$today_ticket_count);
		$this->assign("today_graphic_count",$today_graphic_count);
		$this->assign("today_telephone_count",$today_telephone_count);
		$this->assign("today_reserve_count",$today_reserve_count);
		$this->assign("today_video_count",$today_video_count);
		$this->assign("today_pre_count",$today_pre_count);
		$this->assign("graphic_count",$graphic_count);
		$this->assign("telephone_count",$telephone_count);
		$this->assign("reserve_count",$reserve_count);
		$this->assign("date",$date);
		$this->assign("date1",$date1);
		$this->assign("date2",$date2);
		$this->assign("date3",$date3);
		$this->assign("date4",$date4);
		$this->assign("date5",$date5);
		$this->assign("date6",$date6);
		$this->assign("month",$month);
		$this->assign("month1",$month1);
		$this->assign("month2",$month2);
		$this->assign("month3",$month3);
		$this->assign("month4",$month4);
		$this->assign("month5",$month5);
		$this->assign("month6",$month6);
		$this->assign("month7",$month7);
		$this->assign("month8",$month8);
		$this->assign("month9",$month9);
		$this->assign("month10",$month10);
		$this->assign("month11",$month11);
		$this->assign("today_member_count",$today_member_count);
		$this->assign("today_member_count1",$today_member_count1);
		$this->assign("today_member_count2",$today_member_count2);
		$this->assign("today_member_count3",$today_member_count3);
		$this->assign("today_member_count4",$today_member_count4);
		$this->assign("today_member_count5",$today_member_count5);
		$this->assign("today_member_count6",$today_member_count6);
		$this->assign("today_pharmacy_count",$today_pharmacy_count);
		$this->assign("today_pharmacy_count1",$today_pharmacy_count1);
		$this->assign("today_pharmacy_count2",$today_pharmacy_count2);
		$this->assign("today_pharmacy_count3",$today_pharmacy_count3);
		$this->assign("today_pharmacy_count4",$today_pharmacy_count4);
		$this->assign("today_pharmacy_count5",$today_pharmacy_count5);
		$this->assign("today_pharmacy_count6",$today_pharmacy_count6);
		$this->assign("today_pharmacy_count7",$today_pharmacy_count7);
		$this->assign("today_pharmacy_count8",$today_pharmacy_count8);
		$this->assign("today_pharmacy_count9",$today_pharmacy_count9);
		$this->assign("today_pharmacy_count10",$today_pharmacy_count10);
		$this->assign("today_pharmacy_count11",$today_pharmacy_count11);
		


		//7天的商品订单数
		/*
		$datetime7=time()-24*3600*6;
		$redata=array();
		$numberdata=array();
		for($i=0;$i<7;$i++)
		{
			unset($res);
			$redata[$i]=date('Y-m-d',$datetime7+$i*3600*24);
			$res=$this->order->where('pay_status=1 and '.strtotime($redata[$i]).' < createtime  and createtime <'.(strtotime($redata[$i])+24*3600-1))->count();
			$numberdata[$i]=$res;
		}
		$redata=json_encode($redata);
		$numberdata=json_encode($numberdata);

		
		//12个月的商品成交额
		$reyear=array();
		$numberyear=array();
		for($i=0;$i<12;$i++)
		{
			unset($resyear);
			$reyear[$i]=($i+1).'月';
			unset($BeginDate);
			$BeginDate=date("Y-".($i+1)."-01");
			$resyear=$this->order->where('pay_status=1 and '.strtotime(date("Y-".($i+1)."-01")).' < createtime  and createtime <'.(strtotime("$BeginDate +1 month -1 day")+24*3600-1))->sum('order_price');
			$numberyear[$i]=!empty($resyear) ? $resyear : 0;
		}
		$reyear=json_encode($reyear);
		$numberyear=json_encode($numberyear);
		
		
		
		//7天的门票订单数
		$datetime7ticket=time()-24*3600*6;
		$redataticket=array();
		$numberdataticket=array();
		for($i=0;$i<7;$i++)
		{
			unset($resticket);
			$redataticket[$i]=date('Y-m-d',$datetime7ticket+$i*3600*24);
			$resticket=$this->ticketorder->where('pay_status=1 and '.strtotime($redataticket[$i]).' < createtime  and createtime <'.(strtotime($redataticket[$i])+24*3600-1))->count();
			$numberdataticket[$i]=$resticket;
		}
		$redataticket=json_encode($redataticket);
		$numberdataticket=json_encode($numberdataticket);

		
		//12个月的门票成交额
		$reyearticket=array();
		$numberyearticket=array();
		for($i=0;$i<12;$i++)
		{
			unset($resyearticket);
			$reyearticket[$i]=($i+1).'月';
			unset($BeginDateticket);
			$BeginDateticket=date("Y-".($i+1)."-01");
			$resyearticket=$this->ticketorder->where('pay_status=1 and '.strtotime(date("Y-".($i+1)."-01")).' < createtime  and createtime <'.(strtotime("$BeginDateticket +1 month -1 day")+24*3600-1))->sum('order_price');
			$numberyearticket[$i]=!empty($resyearticket) ? $resyearticket : 0;
		}
		$reyearticket=json_encode($reyearticket);
		$numberyearticket=json_encode($numberyearticket);
		
		
		

		$this->assign("today_member_count",$today_member_count);
		$this->assign("today_goods_count",$today_goods_count);
		$this->assign("today_ticket_count",$today_ticket_count);
		$this->assign("tody_pay_count",$tody_pay_count);
		
		$this->assign("order_not_pay_goods",$order_not_pay_goods);
		$this->assign("order_yes_pay_goods",$order_yes_pay_goods);
		$this->assign("order_count_goods",$order_count_goods);
		$this->assign("pay_count_goods",$pay_count_goods);
		
		
		$this->assign("order_not_pay_ticket",$order_not_pay_ticket);
		$this->assign("order_yes_pay_ticket",$order_yes_pay_ticket);
		$this->assign("order_count_ticket",$order_count_ticket);
		$this->assign("pay_count_ticket",$pay_count_ticket);
		
		$this->assign("redataticket",$redataticket);
		$this->assign("numberdataticket",$numberdataticket);
		
		$this->assign("redata",$redata);
		$this->assign("numberdata",$numberdata);
		
		$this->assign("reyearticket",$reyearticket);
		$this->assign("numberyearticket",$numberyearticket);
		
		$this->assign("reyear",$reyear);
		$this->assign("numberyear",$numberyear);
		*/
		$this->display('welcome');
	}

}
?>