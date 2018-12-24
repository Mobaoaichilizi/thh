<?php
// +----------------------------------------------------------------------
// | 业绩对账
// +----------------------------------------------------------------------
// | Copyright (c) 2017-2018 https://www.lovezhuan.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: TH2 <2791919673@qq.com>
// +----------------------------------------------------------------------
namespace Api\Controller;
use Common\Controller\ApibaseController;
class AccountController extends ApibaseController {
	
        public function _initialize() {
        	parent::_initialize();
            $this->shopuser = D("ShopUser");
            $this->shoprole = D("ShopRole");
        	$this->shoproom = D("ShopRoom");
            $this->orders = D("Orders");
            $this->ordersproject = D("OrdersProject");
        	$this->setting = D("Setting");
            $this->where="chain_id=".$this->chain_id." and shop_id=".$this->shop_id;
        }
        //对账
	public function index()
	{
                $start_time = I('request.start_time');  
                $end_time = I('request.end_time');  
                if($start_time=='' || $end_time=='')
                {
                   $start_time=time()-24*3600*6;
                   $end_time=time();
                  
                }else
                {
                   $start_time=strtotime($start_time);
                   $end_time=strtotime($end_time);
                }
                $orders = $this->orders->where($this->where.' and status=2 and createtime>'.$start_time.' and createtime<'.$end_time)->order('createtime desc,id desc')->select();
                if($orders){
                        foreach ($orders as $key => $value) {
                                $role_id = $this->shopuser->where('id='.$value['shopuser_id'])->getfield('role_id');
                                if($role_id == '0'){
                                        $info[$key]['from'] = '管理员';
                                }else{
                                        $info[$key]['from'] = $this->shoprole->where('id='.$role_id)->getfield('name');
                                }
                                $info[$key]['id'] = $value['id'];
                                $info[$key]['order_sn'] = $value['order_sn'];
                                $info[$key]['createtime'] = date('Y-m-d H:i',$value['createtime']);
                                $info[$key]['pay_way'] = $this->setting->where('id='.$value['pay_type'])->getfield('title'); 
                                $info[$key]['guest_num'] = $this->ordersproject->where('order_id='.$value['id'].' and is_del=0')->count(); 
                                $info[$key]['preferential_amount'] = $value['preferential_amount']; 
                                $info[$key]['pay_amount'] = $value['pay_amount']; 
                                $info[$key]['room_name'] = $this->shoproom->where('id='.$value['room_id'])->getfield('room_name'); 
                        }
                }else{
                        $info = array();
                }
                unset($data);
                $data['code'] = 0;
                $data['msg'] = 'success';
                $data['info'] = $info;
                $data['start_time'] = date("Y-m-d",$start_time);
                $data['end_time'] = date("Y-m-d",$end_time);
                outJson($data); 

	}
        
}
?>