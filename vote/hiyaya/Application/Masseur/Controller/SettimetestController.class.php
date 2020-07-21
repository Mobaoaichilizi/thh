<?php
// +----------------------------------------------------------------------
// | 定时任务
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | Author: Th2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Masseur\Controller;
use Common\Controller\BaseController;
class SettimetestController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->shopappointment = M('ShopAppointment');//预约表
        $this->shopscheduling=M("ShopScheduling"); //排班表
        $this->shoproom=M("ShopRoom"); //房间表
        $this->shopbed=M("ShopBed"); //床位表
        $this->shopmasseur = M("ShopMasseur");//技师表
		$this->orders=M("Orders"); //订单表
		$this->ordersproject=M("OrdersProject"); //订单项目表
		$this->evaluation=M("Evaluation"); //评价表
		$this->shopuser=M("ShopUser"); //管家用户表
    }
    //预约单子，时间一到，系统自动解锁相关资源
    public function unlock()
    {
        $appointments = $this->shopappointment->where('status!=3')->select();
        if(!empty($appointments)){
            foreach ($appointments as $key => $value) {
                if($value['is_lock'] == '1'){
                    if(!empty($value['masseur_id'])){
                        $time = strtotime($value['reservation_time']);
                        if($time<=time()){
                            $this->shopscheduling->where("shop_id=".$value['shop_id']." and masseur_id=".$value['masseur_id']." and start_time < ".$time." and end_time > ".$time)->save(array('is_lock' => 1));
                            $this->shopappointment->where('id='.$value['id'])->save(array('is_lock' => 0));
                        }
                    }
                }
                
            }
        }
       
        
    }
	
	//自动评价
	public function auto_evaluation()
	{
		$result=$this->orders->where("status > 0")->order("createtime asc")->select();
		foreach($result as $val)
		{
			$res_project=$this->ordersproject->where("order_id=".$val['id']." and down_time >0 and is_evaluation=0 and is_del=0")->group("masseur_id")->order("id desc")->select();
			foreach($res_project as $row)
			{
				if((time()-300) > $row['down_time'])
				{
					$this->ordersproject->where("order_id=".$val['id']." and down_time >0 and is_evaluation=0 and is_del=0 and masseur_id=".$row['masseur_id'])->save(array('is_evaluation' => 1));
					unset($data_array);
					$data_array=array(
						'chain_id' => $row['chain_id'],
						'shop_id' => $row['shop_id'],
						'order_id' => $row['order_id'],
						'order_project_id' => $row['id'],
						'score' => 3,
						'lable_list' => 0,
						'content' => '好评',
						'masseur_id' => $row['masseur_id'],
						'createtime' => time(),
						'types' => 2
					);
					$this->evaluation->add($data_array);
				}
			}
		}
	}
	
	//未接单自动提醒管家
	public function auto_push()
	{
		$result=$this->orders->where("status > 0")->order("createtime asc")->select();
		foreach($result as $val)
		{
			$res_project=$this->ordersproject->where("order_id=".$val['id']." and up_time=0 and is_confirm=0 and is_push=0 and is_del=0")->group("masseur_id")->order("id desc")->select();
			foreach($res_project as $row)
			{
				if((time()-300) > $row['createtime'])
				{
					$this->ordersproject->where("order_id=".$val['id']." and up_time=0 and is_confirm=0 and is_push=0 and is_del=0 and masseur_id=".$row['masseur_id'])->save(array('is_push' => 1));
					$room_name = $this->shoproom->where('id='.$val['room_id'])->getField('room_name');
					$shopuser_info = $this->shopmasseur->where('id='.$row['masseur_id'])->find();
					$shop_user_info=$this->shopuser->where("id=".$val['shopuser_id'])->find();
					if($shop_user_info['device']!=''){
						unset($title);
						unset($content);
						$title="【".$room_name."】包间，【".$shopuser_info['nick_name']."】健康师还未接单！";
						$content = "【".$room_name."】包间，【".$shopuser_info['nick_name']."】健康师还未接单！";
						$device[] = $shop_user_info['device'];
						$extra = array("push_type" => 5, "order_id" => $val['id']);
						$audience='{"alias":'.json_encode($device).'}';
						$extras=json_encode($extra);
						$os=$shop_user_info['os'];
						$res=jpush_shop_send($title,$content,$audience,$os,$extras);
					}
				}
			}
		}
	}
	
    
}
?>