<?php
// +----------------------------------------------------------------------
// | 投诉建议接口文件
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: Liuguoqiang <415199201@qq.com>
// +----------------------------------------------------------------------
namespace Doctor\Controller;
use Common\Controller\BaseController;
class SettimetestController extends BaseController {
	function _initialize() {
		parent::_initialize();
		$this->member   = D('Member');
		$this->patient   = D('Patientmember');
		$this->user   = D('User');
		$this->vip   = D('Vip');
		$this->graphic   = D('Graphic');
		$this->reserve   = D('Reserve');
		$this->pre   = D('Pre');
		
	}
	public function member_time(){
		// ignore_user_abort();//关掉浏览器，PHP脚本也可以继续执行. 
		// set_time_limit(0);// 通过set_time_limit(0)可以让程序无限制的执行下去
		//  $interval=60*10;// 每隔半小时运行 
		 $vip_1 = $this->vip->where('setting_id=140 and type_id=1')->getfield('money');
		 $vip_2 = $this->vip->where('setting_id=140 and type_id=2')->getfield('money');
		 $vip_3 = $this->vip->where('setting_id=140 and type_id=3')->getfield('money');
		 $vip_4 = $this->vip->where('setting_id=140 and type_id=4')->getfield('money');
		 $svip_1 = $this->vip->where('setting_id=141 and type_id=1')->getfield('money');
		 $svip_2 = $this->vip->where('setting_id=141 and type_id=2')->getfield('money');
		 $svip_3 = $this->vip->where('setting_id=141 and type_id=3')->getfield('money');
		 $svip_4 = $this->vip->where('setting_id=141 and type_id=4')->getfield('money');
		  
		       $time = time();
		       $due_time = $this->user->where('member_level > 0')->select();
		       foreach ($due_time as $key => $value) {
		         	if($value['is_auto'] == 1){
		         		if($value['time_type'] == 1){
		         			if($value['due_time'] - time() <= 0){
		         				if($value['member_level'] == 1){
		         					if($value['balance'] >= $vip_1){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$vip_1);
		         						$time = strtotime("+30 days",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						
		         						$this->user->save($res);
		         						setpoints($value['id'],2,20,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],20,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}else{
		         					if($value['balance'] >= $svip_1){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$svip_1);
		         						$time = strtotime("+30 days",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,30,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],30,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}
		         				
		         			}
		         		}else if($value['time_type'] == 2){
		         			if($value['due_time'] - time() <= 0){
		         				if($value['member_level'] == 1){
		         					if($value['balance'] >= $vip_2){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$vip_2);
		         						$time = strtotime("+1 year",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,20,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],20,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}else{
		         					if($value['balance'] >= $svip_2){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$svip_2);
		         						$time = strtotime("+1 year",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,30,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],30,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}
		         				
		         			}
		         		}else if($value['time_type'] == 3){
		         			if($value['due_time'] - time() <= 0){
		         				if($value['member_level'] == 1){
		         					if($value['balance'] >= $vip_3){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$vip_3);
		         						$time = strtotime("+90 days",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,20,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],20,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}else{
		         					if($value['balance'] >= $svip_3){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$svip_3);
		         						$time = strtotime("+90 days",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,30,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],30,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}
		         				
		         			}
		         		}else if($value['time_type'] == 4){
		         			if($value['due_time'] - time() <= 0){
		         				if($value['member_level'] == 1){
		         					if($value['balance'] >= $vip_4){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$vip_4);
		         						$time = strtotime("+30 days",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,20,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],20,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}else{
		         					if($value['balance'] >= $svip_4){
		         						$this->user->where("id=".$value['id'])->setDec('balance',$svip_4);
		         						$time = strtotime("+30 days",$value['due_time']);
		         						$res = array(
		         							'id' => $value['id'],
		         							'due_time' => $time,
		         						);
		         						$this->user->save($res);
		         						setpoints($value['id'],2,30,'分','212',0);
										$score = $this->user->where("id=".$value['id'])->getField('score');
										financial_log($value['id'],30,3,$score,'连续包月奖励',8,2);
		         					}else{
		         						unset($res);
					         			$res = array(
					         				'id' => $value['id'],
					         				'member_level' => 0,
					         			);
					         			$this->user->save($res);
		         					}
		         				}
		         				
		         			}
		         		}
		         	}else{
		         		if($value['due_time'] - time() <= 0){
		         			unset($res);
		         			$res = array(
		         				'id' => $value['id'],
		         				'member_level' => 0,
		         			);
		         			$this->user->save($res);
		         		}

		         	}
		         }
 
	}
	public function auto_refund(){
		$graphic_list = $this->graphic->where("is_replay=0 and status=1")->select();
		foreach ($graphic_list as $key => $value) {
		    $t = time()-$value['create_time'];
		    if($t >= 86400){
	    		$res = array(
	    			'status' => 3,
	    		);
	    		$result = $this->graphic->where('id='.$value['id'])->save($res);
	    		$money = $this->graphic->where("id=".$value['id'])->getfield('money');
	    		//自动退款成功后退钱
	    		$patient = $this->graphic->where('id='.$value['id'])->getfield('patientmember_id');
	    		$doctor = $this->graphic->where('id='.$value['id'])->getfield('member_id');
	    		$balance = $this->user->where('id='.$patient)->getfield('balance');
				
				$ba = $balance + $money;
				
	
				unset($res);
				$res = array(
					'balance' => $ba,
				);
				$this->user->where('id='.$patient)->save($res);
				financial_log($patient,$money,3,$ba,'图文咨询退款收益',12);
				$uid_phone = $this->user->where('id='.$patient)->find();
				if($uid_phone['deviceid']!='')
				{
					$title="";
					$content = "亲,您有图文咨询已自动退款，请注意查看！";
					$device[] = $uid_phone['deviceid'];
					// $device[] = '866985035736805';
					$extra = array("type" => "2", "type_id" => $value['id'],'user_type' => 2,'status' => 3);
					$audience='{"alias":'.json_encode($device).'}';
					$extras=json_encode($extra);
					$os=$uid_phone['os'];
					// $os='android';
					$var = jpush_send($title,$content,$audience,$os,$extras);
				
					$res_sys_info=array(
						"title" => $title,
						"description" => $content,
						"send_uid" => $doctor,
						"receive_uid" =>$patient,
						"type_id" => $value['id'],
						"type" => 2,
						"createtime" => time(),
					);
					$this->systemsinfo->add($res_sys_info);

			    }

	    		
		    }

		}
	}
	public function auto_complete(){
		$reserve_list = $this->reserve->where("status in (1,2)")->select();
		foreach ($reserve_list as $key => $value) {
			if($value['morn_after'] == 0){//上午
				if(time() - $value['res_date'] >= 43200){
					$res = array(
						'id' => $value['id'],
						'status' => 6,
					);
					$this->reserve->save($res);
				}
			}else{
				if(time() - $value['res_date'] >= 86400){
					$res = array(
						'id' => $value['id'],
						'status' => 6,
					);
					$this->reserve->save($res);
				}
			}
		}

	}
	public function pre_reward(){
		$doctor_list = $this->member->where('status=1')->select();
		$start_time = date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600));
		$end_time  = date('Y-m-d',(time()+(7-(date('w')==0?7:date('w')))*24*3600)); 
		$mon = strtotime($start_time);
		$sun = strtotime("+1 week -1 second",$mon);
		foreach ($doctor_list as $key => $value) {
			$where['create_time'] = array("between",array($mon,$sun));
			$where['doctor_id'] = array("eq",$value['id']);
			$where['status'] = array("eq","77");
			$count = $this->pre->where($where)->count();
			if($count>=10){
				setpoints($value['user_id'],1,200,'元','213',0);
				$balance = $this->user->where("id=".$value['user_id'])->getField('balance');
				financial_log($value['user_id'],200,3,$balance,'交易处方特殊奖励',8);
			}
		}

	}
	

	
}
?>